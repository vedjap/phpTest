<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Project_user as ProjectUserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::sortable()->paginate(10);

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate(
            [
                'name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]
        );

        $user = new User(
            [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'active' => true
            ]
        );

        try {
            $user->save();
        } catch (\Exception $e) {
            return response()->json($e->getMessage, 500);
        }

        return redirect('/users')->with('success', 'User saved!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->active = $request->has('active');
        echo $request->active;
        $request->validate(
            [
                'name' => 'required|string',
                'email' => 'required|string|email'
            ]
        );

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->active = $request->active;

        try {
            $user->save();
        } catch (\Exception $e) {
            return response()->json($e->getMessage, 500);
        }
        return redirect('/users')->with('success', 'User updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $user = User::find($id);
        $user->delete();

        return redirect('/users')->with('success', 'User successfully disabled!');
    }


    public function listUsers()
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $userCollection = UserResource::collection($users);
        return response()->json($userCollection, 200);
    }

    public function listActiveUsers()
    {
        $users = User::all()->where('active', '==', '1');
        if ($users->isEmpty()) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        $userCollection = UserResource::collection($users);
        return response()->json($userCollection, 200);
    }

    public function findUser($user)
    {
        if (!User::find($user)) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(new UserResource(User::find($user)), 200);
    }

    public function findProjectsUser($user)
    {
        $user = User::find($user);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->projects->isEmpty()) {
            return response()->json(['message' => 'User has not been assigned to any project'], 404);
        }

        $projectsCollection = ProjectUserResource::collection($user->projects);
        return response()->json($projectsCollection, 200);
    }

    public function addUser(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]
        );

        $user = new User(
            [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'active' => true
            ]
        );

        try {
            $user->save();
        } catch (\Exception $e) {
            return response()->json($e->getMessage, 500);
        }

        return response()->json(
            [
                'message' => 'User successfully created',
                'link' => url('/api/users/' . $user->id)
            ]
        );
    }

    public function updateUserInfo(Request $request, $user)
    {
        $request->validate(
            [
                'name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'active' => 'required|boolean'
            ]
        );

        $user = User::find($user);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->active = $request->active;

        try {
            $user->save();
        } catch (\Exception $e) {
            return response()->json($e->getMessage, 500);
        }

        return response()->json(
            [
                'message' => 'User data successfully updated.',
                'link' => url('/api/users/' . $user->id)
            ]
        );
    }

    public function deleteUser($user)
    {
        $user = User::find($user);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->active = false;
        //Bring relationship and modify it
        //$user->projects->
        try {
            $user->save();
        } catch (\Exception $e) {
            return response()->json($e->getMessage, 500);
        }
        return response()->json(
            [
                'message' => 'User successfully disabled.',
                200
            ]
        );
    }
}
