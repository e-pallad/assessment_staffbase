<?php

function getUsers() {
    $url = "https://backend.staffbase.com/api/users";
    $auth = "Basic NWZmMmViNzFhYmRiMjI0ZTBmZmI5NDk3OnNYIXV1ZmphRTRlR1BKRDAxUWpPdUxIK19dZmV3UXp4Ty1UWyx9XXBjcTAuNG5RQ29neWFoXWQucG1mJnRENkM=";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Authorization: ' . $auth
    ));

    $users = json_decode(curl_exec($curl));
    curl_close($curl);

    return $users;
}

?>