<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Friends;
use Auth;

class FriendsController extends Controller
{
    public function search(Request $request){
        
        $users = User::where('name', 'LIKE' , '%'.$request->friends.'%')->where('username', 'LIKE' , '%'.$request->friends.'%')->get();
        if($users->isEmpty()){
            $response = [
                'success' => true,
                'message' => 'No user found!' 
            ];
            return response()->json($response, 200);
        }

        $response = [
            'success' => true,
            'users' => $users,
            'message' => 'Found users!',
        ];
        return response()->json($response, 200);
    }

    public function addFriend($id,Request $request){
        $user = User::find($id);
        if(!$user){
            $response = [
                'success' => true,
                'message' => 'No users found!'
            ];
            return response()->json($response, 200);
        }else{

            if($user->id == Auth::user()->id){
                return response()->json([
                    'success' => true,
                    'message' => 'You can\'t send request to yourself!' 
                ], 200);
            }

            $friend = Friends::create(['sender_id' => Auth::user()->id, 'receiver_id' => $user->id]);
            if($friend){
                $response = [
                    'success' => true,
                    'message' => 'Request sent!'
                ];
                return response()->json($response, 200);
            }else{
                $response = [
                    'success' => true,
                    'message' => 'Something went wrong!'
                ];
                return response()->json($response, 200);
            }
        }
    }

    public function fetchRequests(Request $request){
        $requests = Auth::user()->receivedRequest;
        $requests = $requests->map(function($request){
            $request['sender'] = User::find($request['sender_id']);
            return $request;
        });
        return $requests;
    }

    public function acceptRequest($id){
        $request = Friends::find($id);
        $request->status = 2;
        $request->update();
        return $request;
    }

    public function rejectRequest($id){
        $request = Friends::find($id);
        $request->status = 3;
        $request->update();
        return $request;
    }

    public function friends(){
        $userIds = Friends::where('receiver_id', Auth::user()->id)->where('status',2)->pluck('sender_id')->toArray();
        $receiverId = Friends::where('sender_id', Auth::user()->id)->where('status',2)->pluck('receiver_id')->toArray();
        foreach($receiverId as $id){
            array_push($userIds, $id);
        }
        $userIds = array_unique($userIds);
        $users = [];
        foreach($userIds as $id){
            array_push($users, User::find($id));
        }
        return response()->json([
            'success' => true,
            'message' => 'Your friends list',
            'users' => $users
        ],200);
    }

    public function userProfile($id){

        $user = User::find($id);

        //fetching friends of authenticated user
        $authenticatedUserSenderIds = Friends::where('receiver_id', Auth::user()->id)->where('status',2)->pluck('sender_id')->toArray();
        $authenticatedUserReceiverId = Friends::where('sender_id', Auth::user()->id)->where('status',2)->pluck('receiver_id')->toArray();
        $authenticatedUserFriends = $authenticatedUserSenderIds;
        foreach($authenticatedUserReceiverId as $id){
            array_push($authenticatedUserFriends, $id);
        }
        $authenticatedUserFriends = array_unique($authenticatedUserFriends);

        //fetching friends of searched user
        $searchedUserSenderIds = Friends::where('receiver_id', $user->id)->where('status',2)->pluck('sender_id')->toArray();
        $searchedUserReceiverId = Friends::where('sender_id', $user->id)->where('status',2)->pluck('receiver_id')->toArray();
        $searchedUserFriends = $searchedUserSenderIds;
        foreach($searchedUserReceiverId as $id){
            array_push($searchedUserFriends, $id);
        }
        $searchedUserFriends = array_unique($searchedUserFriends);

        // return response()->json([Auth::user()->id,$authenticatedUserFriends, $user->id, $searchedUserFriends]);

        //mutual friends
        $mutualUsersIds = array_intersect($authenticatedUserFriends, $searchedUserFriends);

        $mutualFriends = User::whereIn('id', $mutualUsersIds)->get();

        //are the 2 users friend?
        $checkCombination1 = Friends::where('sender_id', Auth::user()->id)->where('receiver_id', $user->id)->where('status', 2)->get();
        $checkCombination2 = Friends::where('receiver_id', Auth::user()->id)->where('sender_id', $user->id)->where('status', 2)->get();

        return response()->json([
            'success' => true,
            'mutualFriend' => $mutualFriends,
            'user' => $user,
            'friends' => count($checkCombination1)+count($checkCombination2)
        ], 200);
    }


}
