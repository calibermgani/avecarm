<?php

namespace App\Http\Middleware;
use App\Http\Controllers\DBConnectionController as DBConnectionController;
use Closure;
use Session;
use Log;

class SessionHandler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $db_id=$request->get('practice_dbid');
        Log::info($request->get('practice_dbid'));
        if($request->get('practice_dbid') != null)
        {
            $dbconnection = new DBConnectionController();	
            $dbconnection->connectDB($db_id);
        }


        return $next($request);
    }
}
