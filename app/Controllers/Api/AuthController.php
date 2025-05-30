<?php

namespace App\Controllers\Api;

use App\Models\CustomUserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;
use Exception;

class AuthController extends ResourceController
{
    use ResponseTrait;

    protected $Model;
    protected $format = 'json';

    public function register()
    {
        //

        if (!$this->validate($this->validationCheck())) {

            $msg = $this->validator->getErrors();
            return $this->fail($msg, 400);
        }

        $modelObject = new CustomUserModel();

        $entityObject = new User($this->request->getVar() ?? []); // Create a new User entity

        if ($modelObject->save($entityObject)) {
            $users = auth()->getProvider();
            $user = $users->findById($modelObject->getInsertID());
            // Add to Other group
            $user->addGroup('user');

            $msg = 'User Registered Successfully';
            return $this->respondCreated($msg);
        }

        $msg = 'Failed to Create User';
        return $this->failServerError($msg, 500);
    }

    public function login()
    {
        $validationRules = [
            "username" => "required",
            "password" => "required"
        ];

        if (!$this->validate($validationRules)) {

            $msg = $this->validator->getErrors();
            return $this->fail($msg, 400);
        }

        // Check User Details
        $credentials = [
            "username" => $this->request->getVar("username"),
            "password" => $this->request->getVar("password")
        ];

        try {

            if (auth()->loggedIn()) {

                auth()->logout();
            }

            $loginAttempt = auth()->attempt($credentials);

            if (!$loginAttempt->isOK()) {

                $msg = 'Login gagal!';
                return $this->fail($msg, 400, null, 'Login gagal');
            } else {

                $userId = auth()->user()->id;

                $shieldModelObject = new UserModel;

                $userInfo = $shieldModelObject->findById($userId);

                $tokenInfo = $userInfo->generateAccessToken("12345678sfgfdgffd");

                $raw_token = $tokenInfo->raw_token;

                $data = [
                    'token' => $raw_token,
                    'user' => $userInfo,
                ];

                $msg = 'User logged in!';
                return $this->respond($data, 200, $msg);
            }
        } catch (Exception $ex) {
            $msg = $ex->getMessage();
            return $this->failServerError($msg, 500);
        }
    }

    public function profile()
    {

        $userData = auth("tokens")->user();

        $msg = 'Profile information';
        return $this->respond($userData, 200, $msg);
    }

    public function logout()
    {

        auth()->logout();

        auth()->user()->revokeAllAccessTokens();
        $data = [
            "status" => true,
            "message" => "User logged out"
        ];
        $msg = 'User logged out!';
        return $this->respond($data, 200, $msg);
    }

    private function validationCheck()
    {
        $validationRules = [
            'fullname' => [
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required'   => 'Nama lengkap wajib diisi.',
                    'min_length' => 'Nama lengkap minimal harus terdiri dari 3 karakter.',
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email|min_length[3]|is_unique[auth_identities.secret]',
                'errors' => [
                    'required'    => 'Email wajib diisi.',
                    'valid_email' => 'Format email tidak valid.',
                    'min_length'  => 'Email minimal harus terdiri dari 3 karakter.',
                    'is_unique'   => 'Email sudah terdaftar. Gunakan email lain.',
                ]
            ],
            'username' => [
                'rules' => 'required|min_length[3]|is_unique[users.username]',
                'errors' => [
                    'required'   => 'Username wajib diisi.',
                    'min_length' => 'Username minimal harus terdiri dari 3 karakter.',
                    'is_unique'  => 'Username sudah digunakan. Gunakan username lain.',
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/]',
                'errors' => [
                    'required'    => 'Password wajib diisi.',
                    'min_length'  => 'Password minimal harus terdiri dari 8 karakter.',
                    'regex_match' => 'Password harus mengandung setidaknya satu huruf besar, satu huruf kecil, satu angka, dan satu karakter spesial.',
                ]
            ],
        ];

        return $validationRules;
    }
}
