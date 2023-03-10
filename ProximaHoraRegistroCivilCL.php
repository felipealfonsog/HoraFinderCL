<?php
/*
Initially createde by Sebastian Wilson
Modified by Felipe Alfonso González

-----



  Ayuda a buscar la próxima hora disponible en el Registro Civil.
  Genera un archivo de texto plano donde las columnas son separadas usando $separator .
  Este archivo puede ser cargado posteriormente en Excel, y al ordenar por la columna 'fecha_hora'
  se podrá saber dónde conseguir la primera hora.
  Códigos de trámites (descubiertos hasta ahora):
    5 = primera obtención para extranjeros
    6 = renovar carnet de identidad para chilenos
    8 = solicitud de pasaporte
*/

$start          =   time();

$service_id     =   6; // Código del trámite
$region         =   13; // Metropolitana
//json antiguo
// $base_url       =   'https://agenda.qa.registrocivil.cl/api/backend/';
// API con json ok en url  
$base_url       =   'https://agenda.registrocivil.cl/api/backend/comunas/13';
$separator      =   '|';

$hour_structure =   array(
                        'codigo_oficina',
                        'nombre_oficina',
                        'direccion',
                        'codigo_oficina_horas',
                        'fecha_hora',
                        'fecha',
                        'hora',
                        'cantidad'
                    );

// Títulos
print 'comuna';
foreach( $hour_structure as $item_name ) print $separator . $item_name;

// Buscamos todas las comunas de la región

//$cities_info    =   file_get_contents( $base_url. 'comunas/'. $region );
$cities_info    =   file_get_contents($base_url);
$json_cities    =   json_decode( $cities_info, true, 512, JSON_BIGINT_AS_STRING );



if( !$json_cities ) exit( 'No se pudo obtener la lista de comunas' );

foreach( $json_cities as $city ){
    $city_id        =   $city['codigo_comuna'];
    $city_name      =   $city['nombre_comuna'];

    // Buscamos las oficinas dentro de la comuna donde se puede realizar el trámite
    $offices_info   =   file_get_contents( $base_url . '/oficinas/' . $city_id . '/' . $service_id );
    $json_offices   =   json_decode( $offices_info, true, 512, JSON_BIGINT_AS_STRING );

    if( !$json_offices || $json_offices['code'] < 1 ) continue;

    foreach( $json_offices['oficinas'] as $office ){
        $office_id      =   $office['codigo_oficina'];

        // Revisamos las horas disponibles
        $hours_info     =   file_get_contents( $base_url . /'horas/' . $office_id . '/2/' . $service_id );
        $json_hours     =   json_decode( $hours_info, true, 512, JSON_BIGINT_AS_STRING );

        if( !$json_hours || $json_hours['code'] < 1 ) continue;

        foreach( $json_hours['horas'] as $hour ){
            print PHP_EOL . $city_name;

            foreach( $hour_structure as $item_name ) print $separator . $hour[$item_name];
        }
    }
}

print PHP_EOL . 'FIN. Tiempo total: ' . ( time() - $start ) . ' ssegundos.' . PHP_EOL;

?>
