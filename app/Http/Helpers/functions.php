<?php

function timezone()
{
    return \App\Setting::timezone();
}

function errRes($errors)
{
    $msg = 'The given data was invalid';
    if (!empty($errors) && is_array($errors)) {
        $first = reset($errors);
        if (is_array($first) && !empty($first)) {
            $msg = reset($first);
        } elseif (is_string($first)) {
            $msg = $first;
        }
    }
    
    $eres = [
        'message'=>$msg,
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