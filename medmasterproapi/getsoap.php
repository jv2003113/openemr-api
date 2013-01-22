<?php

//header("Content-Type:text/xml");
$ignoreAuth = true;
require 'classes.php';

$xml_string = "";
$xml_string = "<soaps>";


$token = $_POST['token'];
$visit_id = !empty($_POST['visit_id']) ? $_POST['visit_id'] : -1;

//$token = 'fe15082d987f3fd5960a712c54494a68';
//$visit_id = 5;

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $strQuery = "SELECT fsoap. id,fsoap. date, subjective, objective, assessment, plan, fsoap.user
				FROM `forms` AS f
				INNER JOIN `form_soap` AS fsoap ON f.form_id = fsoap.id
				WHERE `encounter` = {$visit_id}
				AND `form_name` = 'SOAP'
                                ORDER BY id DESC";
//echo $strQuery;exit;
    $result = $db->get_results($strQuery);

    if ($result) {
        newEvent($event = 'soap-record-get', $user, $groupname = 'Default', $success = '1', $comments = $strQuery);
        $xml_string .= "<status>0</status>";
        $xml_string .= "<reason>The Soap Record has been fetched</reason>";

        for ($i = 0; $i < count($result); $i++) {
            $xml_string .= "<soap>\n";

            foreach ($result[$i] as $fieldName => $fieldValue) {
                $rowValue = xmlsafestring($fieldValue);
                $xml_string .= "<$fieldName>$rowValue</$fieldName>\n";
            }
            
            $user_query = "SELECT  `firstname` ,  `lastname` 
                                                    FROM  `medmasterusers` 
                                                    WHERE username LIKE  '{$result[$i]->user}'";
            $user_result = $db->get_row($user_query);
            $xml_string .= "<firstname>{$user_result->firstname}</firstname>\n";
            $xml_string .= "<lastname>{$user_result->lastname}</lastname>\n";
            
            $xml_string .= "</soap>\n";
        }
    } else {
        $xml_string .= "<status>-1</status>";
        $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</soaps>";
echo $xml_string;
?>