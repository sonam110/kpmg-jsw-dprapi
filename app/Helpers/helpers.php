<?php
use App\Models\User;

function prepareResult($error, $data, $msg)
{
    return ['error' => $error, 'data' => $data, 'message' => $msg];
}


function generateRandomString($len = 12) {
    return Str::random($len);
}

function timeDiff($time)
{
    return strtotime($time) - time();
}

function getUser() {
    return auth('api')->user();
}

function checkUserExist($email)
{
   $users = User::select('email')->get();
   foreach ($users as $key => $user) 
   {
     if($email==$user->email)
     {
        return true;
     }
   }
   return false;
}

function validatePassword($val) {
  $re = array();
  if ($val) {
  	// check password must contain at least one number
      if (preg_match('/\d/', $val)) {
        array_push($re, true);
      }
      // check password must contain at least one special character
      if (preg_match('/[!@#$%^&*(),.?":{}|<>]/', $val)) {
        array_push($re, true);
      }
      // check password must contain at least one uppercase letter
      if (preg_match('/[A-Z]/', $val)) {
        array_push($re, true);
      }
      // check password must contain at least one lowercase letter
      if (preg_match('/[a-z]/', $val)) {
        array_push($re, true);
      }
  }
  return count($re) >= 3;
}
