<?php
    # Get all users 
    $url = "https://backend.staffbase.com/api/users";
    $auth = "Basic NWZmMmViNzFhYmRiMjI0ZTBmZmI5NDk3OnNYIXV1ZmphRTRlR1BKRDAxUWpPdUxIK19dZmV3UXp4Ty1UWyx9XXBjcTAuNG5RQ29neWFoXWQucG1mJnRENkM=";
    $limit = 1000;
    $offset = 0;
    $returnArray = array();

    # Setup up a do while loop for pagination - I'm not quite sure if it works 
    do {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url . "?limit=" . $limit . "&offset=" . $offset);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: ' . $auth
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $curlArray = json_decode(curl_exec($curl), TRUE);
        curl_close($curl);

        $returnArray = array_merge($returnArray, $curlArray["data"]);

        $offset = $offset + 1001;

    } while ($offset <= $curlArray["total"]);

    # Filter users for avatar not set and store them in array with the path of the matching image
    # If no matching image is found the user wont get stored in array
    $noAvatarUsers = array();

    # Check for available images in /images folder
    $dir = "./images";
    $images = scandir($dir);

    foreach ($returnArray as $users => $user) {
        # Check if avatar is null
        if (is_null($user["profile"]["avatar"])) {
            # Set the comparission string for image lookup
            # In my case I restriced it to .jpg fileformat
            $lookupImage = $user["firstName"] . $user["lastName"] . ".jpg";
            # Check if matching image exists - if so added user to array
            if (in_array($lookupImage, $images)) {
                $id = $user["id"];
                $imagePath = "./images/" . $lookupImage;
                $name = $user["firstName"] . " " . $user["lastName"];
                array_push($noAvatarUsers, array($id,$imagePath,$name));
            } else {
                continue;
            }
        } else {
            continue;
        }
    }

    # PUT the images to the user 
    if (!empty($noAvatarUsers)) {
        $putUrl = "https://backend.staffbase.com/api/users/";

        foreach ($noAvatarUsers as $key => $value) {
            $putId = $value[0];
            $postData = array('avatar' => curl_file_create($value[1],mime_content_type($value[1]),'avatar.jpg'));

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $putUrl . $putId);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Authorization: ' . $auth
            ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            curl_exec ($curl);

            if(!curl_errno($curl)) {
                $info = curl_getinfo($curl);
                if ($info["http_code"] == 200) {
                    echo "Profilbild erfolgreich zum Benutzkonto von " . $value[2] . " hinzugefügt." . PHP_EOL;
                } else {
                    echo "Hochladen schlug mit HTML-Statuscode " . $info["http_code"] . "fehl." . PHP_EOL;
                }
            }
            
            curl_close($curl);

        }
    } else {
        echo "Keine neuen Bilder hinzugefügt!" . PHP_EOL;
        exit;
    }
?>