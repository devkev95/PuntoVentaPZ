<?php

class Conexionli
	{
		public function Ejecutari($query)
		{
			$con = mysqli_connect('localhost','root','1234') or die ("error en la conexion");
			mysqli_select_db($con,'pizzeria');
//
			$bdq= mysqli_query($con,$query);
			mysqli_close($con);

			return $bdq;



		}
	}
?>