<?php


namespace App\Http\Controllers;
use App\Models\UserJob;
use App\Models\User;
use Illuminate\Http\Response;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use DB;
use Auth;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    use ApiResponser;

    private $request;

    public function _construct(Request $request){
        $this ->request = $request;
    }
    public function getUsers(){
        //$users = User::all();
        //return response()->json($users, 200);
        $users = DB::connection('mysql')
        ->select("Select * from table_userinfo");

        //return response 
        return $this->successResponse($users);
    }
    public function index(){
            $users = User::all();
            return $this->successResponse($users);
    }
    public function add(Request $request){
        $rules = [
            'username' => 'required|max:20',
            'password' => 'required|max:20',
            'gender' => 'required|in:Male,Female',
            'jobid' => 'required|numeric|min:1|not_in:0',
        ];

        $this->validate($request, $rules);

        $userjob = UserJob::findOrFail($request->jobid);
        $user = User::create($request->all());

        return $this->successResponse($user, Response::HTTP_CREATED);
    }

    public function show($id){
        $user = User::where('id', $id)->first();
        if ($user){
            return $this->successResponse($user);
        }
        {
        return $this->ErrorResponse('User ID Does Not Exist', Response::HTTP_NOT_FOUND);
        }  
    }

    public function update(Request $request, $id){
        $rules = [
            'username' => 'max:20',
            'password' => 'max:20',
            'gender' => 'in:Male,Female',
            'jobid' => 'required|numeric|min:1|not_in:0',
        ];

        $this->validate($request, $rules);

        //$user = User::findOrFail($id);
        $user = User::where('userid', $id)->first();
        $userjob = UserJob::findOrFail($request->jobid);

        if ($user){
        $user->fill($request->all());
        // if no changes happen
        if ($user->isClean()) {
            return $this->errorResponse('At least one value must change', 
            Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $user->save();
        return $this->successResponse($user);
        }
    }

    public function delete($id)
    {
        $user = User::where('id', $id)->first();
        if($user){
            $user->delete();
            return $this->successResponse($user);
        }
        {
            return $this->errorResponse('User ID does not exist', Response::HTTP_NOT_FOUND);
        }
    }

    public function authenticate(Request $request){
        $username = $request->input('username');
        $pass = $request->input('password');

        $hashed = Hash::make($pass);
        
        $user= User::where('username', $username)->first();
        if($user && Hash::check($pass, $hashed)){
          
            return response()->json(['message' => 'Access Granted!']);
        } else{
            return response()->json(['message' => 'Access Denied !!! Email or Password is incorrect !']);
            //return "Access Denied !!! Email or Password is incorrect !";
        }
    }

}

?>