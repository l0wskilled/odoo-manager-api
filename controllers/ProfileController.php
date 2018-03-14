<?php

class ProfileController extends ControllerBase
{
    /**
     * Gets profile
     */
    public function index()
    {
        // Verifies if is get request
        $this->initializeGet();

        // gets token
        $token = $this->decodeToken($this->getToken());

        $user = Users::findFirstByUsername(
            $token->username_username,
            array(
                'columns' => 'id, username, firstname, lastname, birthday, phone, mobile, address, city, country, email',
            )
        );
        if (!$user) {
            $this->buildErrorResponse(404, "profile.PROFILE_NOT_FOUND");
        } else {
            $data = $user->toArray();
            $this->buildSuccessResponse(200, "common.SUCCESSFUL_REQUEST", $data);
        }

    }

    /**
     * Updates profile
     */
    public function update()
    {
        // Verifies if is patch request
        $this->initializePatch();

        // Start a transaction
        $this->db->begin();

        if (empty($this->json->getPut("firstname")) || empty($this->json->getPut("lastname"))) {
            $this->buildErrorResponse(400, "common.INCOMPLETE_DATA_RECEIVED");
        } else {
            // gets token
            $token = $this->decodeToken($this->getToken());

            $user = Users::findFirstByUsername($token->username_username);
            if (!$user) {
                $this->buildErrorResponse(404, "profile.PROFILE_NOT_FOUND");
            } else {
                $user->firstname = $this->json->getPut("firstname");
                $user->lastname = $this->json->getPut("lastname");
                $user->email = $this->json->getPut("email");
                $user->phone = $this->json->getPut("phone");
                $user->mobile = $this->json->getPut("mobile");
                $user->address = $this->json->getPut("address");
                $user->birthday = $this->json->getPut("birthday");
                $user->country = $this->json->getPut("country");
                $user->city = $this->json->getPut("city");

                if (!$user->save()) {
                    $this->db->rollback();
                    // Send errors
                    $errors = array();
                    foreach ($user->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $this->buildErrorResponse(400,
                        "profile.PROFILE_COULD_NOT_BE_UPDATED", $errors);
                } else {
                    // Commit the transaction
                    $this->db->commit();

                    $user = Users::findFirstByUsername(
                        $token->username_username,
                        array(
                            'columns' => 'level, username, firstname, lastname, birthday, phone, mobile, address, city, country, email',
                        )
                    );
                    $data = $user->toArray();

                    // Register log in another DB
                    $this->registerLog();

                    $this->buildSuccessResponse(200, "profile.PROFILE_UPDATED",
                        $data);
                }

            }
        }
    }

    /**
     * Changes password
     */
    public function changePassword()
    {
        // Verifies if is patch request
        $this->initializePatch();

        // Start a transaction
        $this->db->begin();

        if (empty($this->json->getPut("current_password")) || empty($this->json->getPut("new_password"))) {
            $this->buildErrorResponse(400, "common.INCOMPLETE_DATA_RECEIVED");
        } else {

            // User token
            $token = $this->decodeToken($this->getToken());

            $user = Users::findFirstByUsername($token->username_username);
            if (!$user) {
                $this->buildErrorResponse(400, "common.THERE_HAS_BEEN_AN_ERROR");
            } else {
                // if old password matches
                if (!password_verify($this->json->getPut("current_password"),
                    $user->password)) {
                    $this->buildErrorResponse(400,
                        "change-password.WRONG_CURRENT_PASSWORD");
                } else {
                    // Encrypts temporary password
                    $password_hashed = password_hash($this->json->getPut("new_password"),
                        PASSWORD_BCRYPT);
                    $user->password = $password_hashed;
                    if (!$user->save()) {
                        $this->db->rollback();
                        // Send errors
                        $errors = array();
                        foreach ($user->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $this->buildErrorResponse(400,
                            "change-password.PASSWORD_COULD_NOT_BE_UPDATED",
                            $errors);
                    } else {
                        // Commit the transaction
                        $this->db->commit();

                        // Register log in another DB
                        $this->registerLog();

                        $this->buildSuccessResponse(200,
                            "change-password.PASSWORD_SUCCESSFULLY_UPDATED");
                    }
                }
            }
        }
    }
}
