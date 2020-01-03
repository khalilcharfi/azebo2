<?php
/**
*
* azebo2 is an application to print working time tables
* Copyright(C) 2019  Emanuel Minetti
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version .
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE . See the
* GNU General Public License for more details .
*
* You should have received a copy of the GNU General Public License
* along with this program .  If not, see < https://www.gnu.org/licenses/>.
*
* @author Emanuel Minetti < e . minetti@posteo . de >
* @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
* @copyright Copyright(c) 2019 Emanuel Minetti
* @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
*/


namespace Login\Controller;


use Login\Model\UserTable;
use RuntimeException;
use Service\AuthorizationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class LoginController extends AbstractActionController
{
    private $table;

    public function __construct(UserTable $table)
    {
        $this->table = $table;
    }

    public function loginAction()
    {
        $request = $this->getRequest();
        $content = $request->getContent();
        $requestData = json_decode($content);
        $declineRequest = new JsonModel([
            'success' => false
        ]);
        // validate request method
        if ($request->getMethod() !== 'POST') {
            return $declineRequest;
        }

        // filter request data
        $username = trim($requestData->username);
        $password = trim($requestData->password);

        // validate request data
        if (mb_strlen($username) > 30 || mb_strlen($username) > 30) {
            return $declineRequest;
        }

        try {
            $user = $this->table->getUserByUsername($username);
        } catch (RuntimeException $e) {
            return $declineRequest;
        }

        // authenticate
        if ($user->verifyPassword($password)) {
            unset($user->password_hash);
            $expire = time() + AuthorizationService::EXPIRE_TIME;
            $jwt = AuthorizationService::getJwt($expire, $user->id);
            return new JsonModel([
                'success' => true,
                'data' => [
                    'jwt' => $jwt,
                    'user' => $user->getArrayCopy(),
                    'expire' => $expire,
                ],
            ]);
        } else { // not authenticated
            return $declineRequest;
        }
    }

}
