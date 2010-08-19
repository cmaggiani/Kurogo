<?php

require_once LIBDIR . "/LdapWrapper.php";

$content = '';

$displayFields = array(
  'sn' => 'surname',
  'givenname' => 'givenname',
  'cn' => 'name',
  'title' => 'title',
  'unit' => 'unit',
  'description' => 'description',
  'uid' => 'id',
  'telephonenumber' => 'phone',
  'facsimiletelephonenumber' => 'fax',
  'mail' => 'email',
  'postaladdress' => 'office',
  );

switch ($_REQUEST['command']) {
  case 'details':
    if (isset($_REQUEST['uid'])) {
      $ldap = new LdapWrapper();
      $result = $ldap->loopkupUser($_REQUEST['uid']);
      if ($result) {
        $content = json_encode($result);
      } else {
        $result = array('error' => $ldap->gerError());
        $content = json_encode($result);
      }
    }
    break;
  case 'search':
    if (isset($_REQUEST['q'])) {
      $ldap = new LdapWrapper();
      $ldap->buildQuery($_REQUEST['q']);
      $people = $ldap->doQuery();
      if ($people) {
        $results = array();
        foreach ($people as $person) {
          $result = array();
          foreach ($displayFields as $ldapField => $displayField) {
            $value = $person->getField($ldapField);
            if ($value) {
              $result[$ldapField] = $value;
            }
          }
          $results[] = $result;
        }
        $content = json_encode($results);
      } elseif (is_array($people)) { 
        // empty arrays seem to return true to === FALSE 
        $result = array('error' => 'Nothing Found');
	$content = json_encode($result);
      } else {
        $result = array('error' => $ldap->getError());
	$content = json_encode($result);
      }
    }
    break;
  case 'displayFields':
    $content = json_encode($displayFields);
    break;
  default:
    break;
}


header('Content-Length: ' . strlen($content));
echo $content;