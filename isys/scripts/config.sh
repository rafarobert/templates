export XDEBUG_CONFIG="idekey=123"
export ESTIC_ORIGIN="isys/scripts"
export SERVER_NAME="local.estic.com.bo"
export SERVER_PORT="80"
export XDEBUG_CONFIG="remote_enable=1 remote_mode=req remote_port=9000 remote_host=127.0.0.1 remote_connect_back=0"
export QUERY_STRING="start_debug=1&debug_host=127.0.0.1&no_remote=1&debug_port=10137&debug_stop=1&email=rafael@estic.com.bo&password=123&login=ingresar"

php -B "\$_REQUEST = array('email' => 'rafael@estic.com.bo', 'password' => '123', 'login' => 'ingresar');" -F ../../index.php sys/config/build
echo -e "\012"
cd ../..
composer update
cd isys/scripts
