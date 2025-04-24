<?php

    // Be sure to include the file you've just downloaded
    require_once('AfricasTalkingGateway.php');
    // Specify your authentication credentials
    $username   = "CMSApp";
    $apikey     = "a2a87fa7c0d3d06e6b11d8febae12f958c1250b956f07a6501dc08ae73870662";
    // Specify the numbers that you want to send to in a comma-separated list
    // Please ensure you include the country code (+254 for Kenya in this case)
            $recipients=$phone;
    // And of course we want our recipients to know what we really do
           $message  =$text;
    // Create a new instance of our awesome gateway class
    $gateway    = new AfricasTalkingGateway($username, $apikey);
    /*************************************************************************************
      NOTE: If connecting to the sandbox:
      1. Use "sandbox" as the username
      2. Use the apiKey generated from your sandbox application
         https://account.africastalking.com/apps/sandbox/settings/key
      3. Add the "sandbox" flag to the constructor
      $gateway  = new AfricasTalkingGateway($username, $apiKey, "sandbox");
    **************************************************************************************/
    // Any gateway error will be captured by our custom Exception class below,
    // so wrap the call in a try-catch block
    try
    {
      // Thats it, hit send and we'll take care of the rest.
      $responses = $gateway->sendMessage($recipients, $message);

      foreach($responses as $response) {
              // status is either "Success" or "error message"
        // echo " Number: " .$response->number;
        // echo " Status: " .$response->status;
        // echo " StatusCode: " .$response->statusCode;
        // echo " MessageId: " .$response->messageId;
        // echo " Cost: "   .$response->cost."\n";
      }
      // Retrieve the response ---Ngeno
    }
    catch ( AfricasTalkingGatewayException $e )
    {
      echo " Oooops! Encountered an error while sending: ".$e->getMessage();
    }

?>
