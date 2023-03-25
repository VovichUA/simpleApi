<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\RegisterRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserRegistered;

class RegisterController extends Controller
{

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register api
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $input = $request->all();
        $input['password'] = bcrypt($request->get('password'));
        $user = $this->userRepository->create($input);

        // Sending mail
        Mail::to($user->getEmail())->queue(new UserRegistered($user));

        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->getName();

        return response()->json(['success'=>$success], 200);
    }

}