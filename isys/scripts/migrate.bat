SET XDEBUG_CONFIG="idekey=123"
SET ESTIC_ORIGIN="isys/scripts"
SET SERVER_NAME="local.estic.com.bo"
SET SERVER_PORT="80"
SET XDEBUG_CONFIG="remote_enable=1 remote_mode=req remote_port=9000 remote_host=127.0.0.1 remote_connect_back=0"
SET QUERY_STRING="start_debug=1&debug_host=127.0.0.1&no_remote=1&debug_port=10137&debug_stop=1&email=rafael@estic.com.bo&password=123&login=ingresar"

IF $# == 0
then
  echo "Debes introducir un modulo o submodulo"
  exit 1
else
  if test $# = 1
  then
    php -B "\$_REQUEST = array('email' => 'rafael@estic.com.bo', 'password' => '123');" -F ../../index.php sys/migrate/$1
    cd ../..
    composer update
    cd isys/scripts
    exit 1
  else
    if test $# = 2
    then
      php -B "\$_REQUEST = array('email' => 'rafael@estic.com.bo', 'password' => '123');" -F ../../index.php sys/migrate/$1/$2
      cd ../..
      composer update
        cd isys/scripts
      exit 1
    fi
  fi
fi
