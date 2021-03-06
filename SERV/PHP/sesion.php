<?php
class sesion {
    static $autenticado = false;
    static $autorizado = false; // Cumplio con los 'permisos' necesarios del área
    
    static function preparar() {
        
        session_name("RSVSESION");
        session_start();
        
       // Verificar si esta iniciado
        if ( @$_SESSION['iniciado'] === true )
        {
            self::$autenticado = true;
            $_SESSION['rnd'] = time();
            $cookieLifetime = 365 * 24 * 60 * 60; // A year in seconds
            setcookie(session_name(),session_id(),time()+$cookieLifetime);
        }
    }
    
    static function verificar($permisos) {

        if (defined('USAR_AUT') && USAR_AUT === false )
        {
            self::$autorizado = true;
            return true;
        }
        
        if ( self::$autenticado == false || !is_array(@$_SESSION['permisos']) || count(@$_SESSION['permisos']) == 0 || !is_array($permisos) || count($permisos) == 0)
        {
            self::$autorizado = false;
            return false;
        }
        
        self::$autorizado = ( count(array_intersect($permisos, @$_SESSION['permisos'])) == count($permisos) );
        
    }
    
    static function iniciar($usuario, $clave)
    {
        $usuario = db_codex(strtolower($usuario));
        $clave = db_codex($clave);
        
        $c = "SELECT * FROM usuarios WHERE LCASE(usuario) = '$usuario' AND clave = SHA1('$clave') LIMIT 1";
        $rUsuario = db_consultar($c);
        if (db_num_resultados($rUsuario) > 0)
        {
            $_SESSION['datos'] = db_fetch($rUsuario);
            
            $c = 'SELECT permiso FROM usuarios_permisos WHERE ID_usuarios = "'.$_SESSION['datos']['ID_usuarios'].'"';
            $rPermisos = db_consultar($c);
            
            while ($fPermiso = db_fetch($rPermisos))
            {
                $_SESSION['permisos'][] = $fPermiso['permiso'];
            }
         
            self::$autenticado = true;
            $_SESSION['iniciado'] = true;
            return true;
        } else {
            self::terminar();
        }
        
        return false;
    }
    
    static function terminar()
    {
        self::$autenticado = false;
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(),'',0,'/');
    }
}

sesion::preparar();

if (defined('USAR_AUT') && USAR_AUT === false )
{
    sesion::$autenticado = true;
    sesion::$autorizado = true;
    return;
}