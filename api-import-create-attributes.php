<?php

define( 'FILE_TO_IMPORT', 'colores.json' );

require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

if ( ! file_exists( FILE_TO_IMPORT ) ) :
	die( 'Unable to find ' . FILE_TO_IMPORT );
endif;	

// ====================================================================================
// Conexión API destino
// ====================================================================================

$url_API_woo = 'https://yourdomain.com';
$ck_API_woo = 'ck_xxx';
$cs_API_woo = 'cs_xxx';

$woocommerce = new Client(
    $url_API_woo,
    $ck_API_woo,
    $cs_API_woo,
    [
        'wp_api' => true,
        'version' => 'wc/v3',
    ]
);

// ====================================================================================
// Conexión API origen
// ====================================================================================


$url_API="http://api.argentina.cdopromocionales.com/v1/products?auth_token=yBNPpxn7HDpxQDNDQDHhrw";
 

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$url_API);
curl_setopt($ch, CURLOPT_TIMEOUT,5000); // 1000 seconds

print("✔  HORA INICIO LECTURA API: ". date("h:i:sa")." \n");
echo "➜ Obteniendo datos origen ... \n";
print("\n");

$items_origin = curl_exec($ch);
curl_close($ch);

if ( ! $items_origin ) {
    exit('❗Error en API origen');
}

$items_origin = json_decode($items_origin, true);

try {

    
    foreach($items_origin as $product)
    {
        $sku = $product['code'];
        $array_colores = $product['variants'];
        $arrayColorLength = count($array_colores);

        for($i=0; $i<$arrayColorLength; $i++)
        {
            $colorix = strtolower($array_colores[$i]['color']);

            switch ($colorix) {
                case "verde     ":
                   $colorix = "verde";
                  break;
              }

            $item_colores_var[] = $colorix;
            $colorix = "";
        }
    }


     sort($arrayDeColores);
     foreach($arrayDeColores as $colorcito)
     {
         echo $colorcito . "\n";
     }
     print("\n");


    
        $attribute_data = array(
            'name' => 'colores',
            'slug' => 'pa_color_1',
            'type' => 'select',
            'order_by' => 'menu_order',
            'has_archives' => true
        );


		$wc_attribute = $woocommerce->post( 'products/attributes', $attribute_data );

		if ( $wc_attribute ) :

            status_message('Atributo ID: '. $wc_attribute->id );
            print("\n");
            
            $id_atributo = $wc_attribute->id;
            
             
            foreach($arrayDeColores as $colorProd):        

                $term = $colorProd;
			    $attribute_term_data = array(
					'name' => $term
				);

				$wc_attribute_term = $woocommerce->post( 'products/attributes/'. $wc_attribute->id .'/terms', $attribute_term_data );

				if ( $wc_attribute_term ) :

					status_message( 'Termino ID es: '. $wc_attribute_term->id );
					status_message( 'Termino: '. $term);
                    print("\n");
                    
					$term = "";

				endif;	
				
			endforeach;

		endif;		

} 
catch ( HttpClientException $e ) 
{
    echo $e->getMessage(); // Error message
}

/**
 * Print status message.
 *
 * @param  string $message
 * @return string
*/
function status_message( $message ) {
	echo $message . "\r\n";
}

?>