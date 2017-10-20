<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserIllegal extends Model
{
    //
    /**
     * 可以被批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = ['user_id', 'illegal_id', 'cause', 'time'];

    public  function  user(){
        return $this->belongsTo('App\User', 'user_id', 'id');
    }


    public  function  illegal(){
        return $this->belongsTo('App\Illegal', 'illegal_id', 'id');
    }


    //获取用户违规信息
    public static function getIllegalUserInfo(){
        //获取有未结清违规记录的人员信息和违规记录
        $userIllegals = User::with([
            'grouping' ,
            'illegals' => function($query) {
                $query->with('illegal')->where('punish_id', '=', 0);
            }
        ])->whereHas('illegals', function ($query) {
            $query->where('punish_id', '=', 0);
        })->get(['id', 'name', 'grouping_id']);



        $data = collect([]);

        foreach ($userIllegals as $user){
            foreach ($user->illegals->groupBy('illegal_id') as $punishIllegal){
                $data->push([
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'grouping'  => $user->grouping->toArray(),
                    'type'      => $punishIllegal->first()->illegal->name,
                    'illegal'   => $punishIllegal->first()->illegal->toArray(),
                    'count'     => count($punishIllegal),
                    'illegals'  => $punishIllegal->toArray(),
                ]);
            }
        }


        return array_values(($data->sortByDesc('count'))->toArray());
    }
}