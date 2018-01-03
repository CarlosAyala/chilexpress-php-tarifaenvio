<?php

/**
 * Clase para calculo de tarifa de chilexpress
 */
Class Chileexpress_Tarificacion{
    
    /**
     * Retorna el valor del shipping de Chilexpress
     * @param string $origen
     * @param string $destino
     * @param int $peso
     * @param int $alto
     * @param int $ancho
     * @param int $largo
     * @return mixed
     */
    public function get($origen, $destino, $peso, $alto, $ancho, $largo){
        $route = "TarificarCourier";
        
        $method = 'reqValorizarCourier';
	$data = [ 
            'CodCoberturaOrigen' => $origen,
            'CodCoberturaDestino' => $destino,
            'PesoPza' => $peso,
            'DimAltoPza' => $alto,
            'DimAnchoPza' => $ancho,
            'DimLargoPza' => $largo 
        ];
        
        $client_options = array(
            'login'    => "UsrTestServicios",
            'password' => "U$\$vr2\$tS2T",
            'cache_wsdl' => WSDL_CACHE_NONE,
            'exceptions' => 0,
            'stream_context' => stream_context_create(array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false, 
                    'allow_self_signed' => true //can fiddle with this one.
                )
            ))
        );
       
        $client = new \SoapClient(__DIR__ . "/wsdl/WSDL_Tarificacion_QA.wsdl", $client_options );
        $header_body = array(
            'transaccion' => array(
                'fechaHora'            => date( 'Y-m-d\TH:i:s.Z\Z', time() ),
                'idTransaccionNegocio' => '0',
                'sistema'              => 'TEST',
                'usuario'              => 'TEST'
            )
        );
        
        $header = new \SoapHeader( "http://www.chilexpress.cl/TarificaCourier/", 'headerRequest', $header_body );
        $client->__setSoapHeaders( $header );
        
        $result = $client->__soapCall( $route, [ $route => [ $method => $data ] ] );
        
        if (is_soap_fault($result)) {
            return false;
        } else {
            $valor = null;
            if($result->respValorizarCourier->CodEstado == 0){
                foreach( $result->respValorizarCourier->Servicios as $servicios){

                    if(is_null($valor)){
                        $valor = $servicios->ValorServicio;
                    }

                    if($valor > $servicios->ValorServicio){
                        $valor = $servicios->ValorServicio;
                    }
                }
            }
            if(is_null($valor)){
                return false;
            } else {
                return $valor;
            }
        }
    }
}
