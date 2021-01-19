<?php
namespace Ropeg\Sso;

define('URL_PROTOCOL', 'https://');
define('APP_SESSION_NAME', 'appsession');
define('APP_USER_MANAGEMENT_URL', URL_PROTOCOL.'sso.kemenag.go.id');
define('APP_USER_MANAGEMENT_SIGN_IN', APP_USER_MANAGEMENT_URL.'/auth/signin');
define('APP_USER_MANAGEMENT_SIGN_OUT', APP_USER_MANAGEMENT_URL.'/auth/signout');
define('TOKEN', FALSE);
define('APP_API_ENDPOINT', APP_USER_MANAGEMENT_URL.'/api');

class Sso
{
  public $callback=false;

  function __construct() {
    $this->callback = $callback;
    if($_GET['ssoverify'])
    {
      $this->verify($_GET['ssoverify']);
    }
  }

  function set_callback($url) {
    $this->callback = $url;
  }

  public function verify($value='')
  {

  }

  public static function getUser() {
    if(TOKEN)
    {
      $result = api(
          'GET',
          APP_API_ENDPOINT,
          'getuser'
      );

      return $result;
    }else{
      header('location:'.get_signin_url());
    }
  }

  /**
   * Gets sign-in url.
   *
   * @return string Returns sign-in url.
   */
  function get_signin_url()
  {
      return get_url(APP_USER_MANAGEMENT_SIGN_IN);
  }

  /**
   * Gets sign-out url.
   *
   * @return string Returns sign-out url.
   */
  function get_sign_out_url()
  {
      return get_url(APP_USER_MANAGEMENT_SIGN_OUT);
  }

  /**
   * Redirects to login page if user is not authenticated.
   */
  function sign_in_required()
  {
    header('location:'.get_signin_url());
  }

  /**
   * Calls API methods using token based on basic authorization.
   *
   * @param string $method     GET, POST, PUT
   * @param string $base_url   API base url
   * @param string $api_user   API user string
   * @param string $api_token  API token string
   * @param string $api_method API method name
   * @param bool   $data       Data which will be send to API method
   *
   * @link http://php.net/manual/en/function.curl-error.php
   *
   * @return obejct Returns object which has been created from received JSON response.
   */
  function api($method, $base_url, $api_method, $data = false, $assoc = false)
  {
      $curl = curl_init();
      $url = $base_url.'/'.$api_method;
      $qry_str = '';

      switch ($method) {
          case 'GET':
              if ($data) {
                  $url = $url.'?'.$qry_str = http_build_query($data);
              }
              break;
          case 'POST':
              curl_setopt($curl, CURLOPT_POST, 1);
              if ($data) {
                  curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
              }
              break;
          case 'PUT':
              curl_setopt($curl, CURLOPT_PUT, 1);
              break;
          default:
              if ($data) {
                  $url = sprintf('%s?%s', $url, http_build_query($data));
              }
      }

      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Authorization: Bearer '. TOKEN]);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl, CURLOPT_TIMEOUT, 5);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

      $result = curl_exec($curl);
      $result = trim($result);

      curl_close($curl);

      return json_decode($result, $assoc);
  }
}
