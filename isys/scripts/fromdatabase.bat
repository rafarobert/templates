set XDEBUG_CONFIG="idekey=123"
set ESTIC_ORIGIN="isys/scripts"
set SERVER_NAME="local.estic.com.bo"
set SERVER_PORT="80"
set XDEBUG_CONFIG="remote_enable=1 remote_mode=req remote_port=9000 remote_host=127.0.0.1 remote_connect_back=0"
set QUERY_STRING="start_debug=1&debug_host=127.0.0.1&no_remote=1&debug_port=10137&debug_stop=1&email=rafael@estic.com.bo&password=123&login=ingresar"

php -B "$_REQUEST = array('email' => 'rafael@estic.com.bo', 'password' => '123');" -F ../../index.php sys/migrate/fromdatabase
echo -e "\012"
cd ../..
composer update && cd isys/scripts
