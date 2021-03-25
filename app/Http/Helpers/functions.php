<?php

function timezone()
{
    return \App\Setting::timezone();
}

function errRes($errors)
{
    $eres = [
        'message'=>'The given data was invalid',
        'errors'=>[]
    ];
    
    foreach($errors as $f=>$errmsgs)
    {
        $eres['errors'][] = [
            'error'=>$f,
            'message'=>$errmsgs
        ];
    }
    return response()->json($eres, 422);
}

?>