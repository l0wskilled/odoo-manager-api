<?php

class ServersController extends ControllerBase
{
    /**
     * Gets view
     */
    public function index()
    {
        // Verifies if is get request
        $this->initializeGet();

        // Init
        $rows = 5;
        $order_by = 'name asc';
        $offset = 0;
        $limit = $offset + $rows;

        // Handles Sort querystring (order_by)
        if ($this->request->get('sort') != null && $this->request->get('order') != null) {
            $order_by = $this->request->get('sort') . " " . $this->request->get('order');
        }

        // Gets rows_per_page
        if ($this->request->get('limit') != null) {
            $rows = $this->getQueryLimit($this->request->get('limit'));
            $limit = $rows;
        }

        // Calculate the offset and limit
        if ($this->request->get('offset') != null) {
            $offset = $this->request->get('offset');
            $limit = $rows;
        }

        // Init arrays
        $conditions = [];
        $parameters = [];

        // Filters simple (no left joins needed)
        if ($this->request->get('filter') != null) {
            $filter = json_decode($this->request->get('filter'), true);
            foreach ($filter as $key => $value) {
                array_push($conditions, $key . " LIKE :" . $key . ":");
                $parameters = $this->array_push_assoc($parameters, $key, "%" . trim($value) . "%");
            }
            $conditions = implode(' AND ', $conditions);
        }

        // Search DB
        $server = Servers::find(
            array(
                $conditions,
                'bind' => $parameters,
                'order' => $order_by,
                'offset' => $offset,
                'limit' => $limit,
            )
        );

        // Gets total
        $total = Servers::count(
            array(
                $conditions,
                'bind' => $parameters,
            )
        );

        if (!$server) {
            $this->buildErrorResponse(404, 'common.NO_RECORDS');
        } else {
            $data = [];
            $data = $this->array_push_assoc($data, 'rows_per_page', $rows);
            $data = $this->array_push_assoc($data, 'total_rows', $total);
            $data = $this->array_push_assoc($data, 'rows', $server->toArray());
            $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
        }
    }

    public function create()
    {
        // Verifies if is post request
        $this->initializePost();

        // Start a transaction
        $this->db->begin();

        if (empty($this->json->getPost('name')) || empty($this->json->getPost('localeIp'))) {
            $this->buildErrorResponse(400, 'common.INCOMPLETE_DATA_RECEIVED');
        } else {
            $name = trim($this->json->getPost('name'));
            $server = Servers::findFirstByName($name);
            if ($server) {
                $this->buildErrorResponse(409, 'common.THERE_IS_ALREADY_A_RECORD_WITH_THAT_NAME');
            } else {
                $newServer = new Servers();
                $newServer->name = $name;
                $newServer->localeIp = trim($this->json->getPost('localeIp'));
                $newServer->remoteIp = trim($this->json->getPost('remoteIp'));
                if (!$newServer->save()) {
                    $this->db->rollback();
                    // Send errors
                    $errors = array();
                    foreach ($newServer->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $this->buildErrorResponse(400, 'common.COULD_NOT_BE_CREATED', $errors);
                } else {
                    // Commit the transaction
                    $this->db->commit();
                    // Register log in another DB
                    $this->registerLog();

                    $data = $newServer->toArray();
                    $this->buildSuccessResponse(201, 'common.CREATED_SUCCESSFULLY', $data);
                }
            }
        }
    }

    public function get($id)
    {
        // Verifies if is get request
        $this->initializeGet();

        $server = Servers::findFirstById($id);
        if (!$server) {
            $this->buildErrorResponse(404, 'common.NOT_FOUND');
        } else {
            $data = $server->toArray();
            $data = $this->array_push_assoc($data, "chipSource", $this->getChipSource($server));
            $data = $this->array_push_assoc($data, "chips", $this->getChips($server));
            $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $data);
        }
    }

    public function update($id)
    {
        // Verifies if is patch request
        $this->initializePatch();

        // Start a transaction
        $this->db->begin();

        /** @var Servers $server */
        $server = Servers::findFirstById($id);
        if (!$server) {
            $this->buildErrorResponse(404, 'common.NOT_FOUND');
        } else {
            $name = trim($this->json->getPut('name'));
            $serverCheck = Servers::findFirstByName($name);
            if ($serverCheck && $serverCheck->id != $id) {
                $this->buildErrorResponse(409,
                    'common.THERE_IS_ALREADY_A_RECORD_WITH_THAT_NAME');
            } else {
                if (empty($this->json->getPut('name'))
                    || empty($this->json->getPut('localeIp'))
                ) {
                    $this->buildErrorResponse(400,
                        'common.INCOMPLETE_DATA_RECEIVED');
                } else {
                    $server->name = $name;
                    $server->localeIp = trim($this->json->getPut('localeIp'));
                    $server->remoteIp = trim($this->json->getPut('remoteIp'));
                    $old = ServersUsers::findByServer($server->id);
                    if (!$old->delete()) {
                        $this->db->rollback();
                        $errors = array();
                        foreach ($old->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $this->buildErrorResponse(400, 'common.COULD_NOT_BE_UPDATED', $errors);
                    }
                    foreach ($this->json->getPut("chips") as $chip) {
                        $s = new ServersUsers();
                        $s->server = $server->id;
                        $s->user = $chip["id"];
                        if (!$s->save()) {
                            $this->db->rollback();
                            $errors = array();
                            foreach ($s->getMessages() as $message) {
                                $errors[] = $message->getMessage();
                            }
                            $this->buildErrorResponse(400, 'common.COULD_NOT_BE_UPDATED', $errors);
                        }
                    }
                    if (!$server->save()) {
                        $this->db->rollback();
                        // Send errors
                        $errors = array();
                        foreach ($server->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $this->buildErrorResponse(400, 'common.COULD_NOT_BE_UPDATED', $errors);
                    } else {
                        // Commit the transaction
                        $this->db->commit();
                        // Register log in another DB
                        $this->registerLog();
                        $data = $server->toArray();
                        $data = $this->array_push_assoc($data, "chipSource", $this->getChipSource($server));
                        $data = $this->array_push_assoc($data, "chips", $this->getChips($server));
                        $this->buildSuccessResponse(200, 'common.UPDATED_SUCCESSFULLY', $data);
                    }
                }
            }
        }
    }

    public function delete($id)
    {
        // Verifies if is get request
        $this->initializeDelete();

        // Start a transaction
        $this->db->begin();

        /** @var Servers $server */
        $server = Servers::findFirstById($id);
        if (!$server) {
            $this->buildErrorResponse(404, 'common.NOT_FOUND');
        } else {
            if (!$server->delete()) {
                $this->db->rollback();
                // Send errors
                $errors = array();
                foreach ($server->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $this->buildErrorResponse(400, 'common.COULD_NOT_BE_DELETED',
                    $errors);
            } else {
                // Commit the transaction
                $this->db->commit();
                // Register log in another DB
                $this->registerLog();

                $this->buildSuccessResponse(200, 'common.DELETED_SUCCESSFULLY');
            }
        }
    }

    private function getChipSource($server)
    {
        $alreadySet = ServersUsers::findByServer($server->id);
        $availableAsChips = Users::query()
            ->columns(["id, username"]);
        if ($alreadySet->valid()) {
            $availableAsChips->notInWhere(
                "id",
                array_map("intval", array_column($alreadySet->toArray(), "id"))
            );
        }
        $result = $availableAsChips->execute();
        return $result->toArray();
    }

    private function getChips($server) {
        $chips = [];
        foreach ($server->Users as $user) {
            $chips[] = ["id" => $user->id, "username" => $user->username];
        }
        return $chips;
    }
}
