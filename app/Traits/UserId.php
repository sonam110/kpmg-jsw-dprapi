<?php
namespace App\Traits;
use Illuminate\Database\Eloquent\Builder;

trait UserId {

	protected static function bootUserId()
    {
    	if (auth()->guard('api')->check()) 
    	{
	        // if user is superadmin - usertype admin OT top_most_parent_id=1
	        if ((auth()->guard('api')->user()->role_id==1)) 
	        {
	        	/*static::creating(function ($model) {
		            $model->user_id = auth()->guard('api')->user()->user_id;
		        });*/
	        }
	        else
	        {	        	
        		// static::creating(function ($model) {
		        //     $model->user_id = auth()->guard('api')->user()->user_id;
		        // });
        		// static::addGlobalScope('user_id', function (Builder $builder) {
	         //        $builder->where('user_id', auth()->guard('api')->user()->user_id);
	         //    });

        		// static::creating(function ($model) {
		        //     $model->user_id = auth()->guard('api')->user()->id;
		        // });
	        	/*if ((auth()->guard('api')->user()->vendor_id !='')) 
	        	{
	        		static::addGlobalScope('user_id', function (Builder $builder) {
		                $builder->where('user_id', auth()->guard('api')->user()->id);
		            });
	        	} else{
			        

	        	}*/
	        }
	    }
    }
}