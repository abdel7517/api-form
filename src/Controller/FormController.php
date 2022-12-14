<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

class FormController extends AbstractController
{
    /**
     * @Route("/form", name="app_form")
     */
    public function index(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        $app_base_url = "https://boom.vezoa.com/";

        $source_code = "NATIVE-OUTBRAIN";
    
        $activity_code = "PAC";
    
        $lead_info = [
            'name' => $payload['name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'zipcode' => $payload['zipcode'],
            'house_ownership_type' => $payload['house_ownership_type'],
            'house_type' => $payload['house_type'],
            'heating_type' => $payload['heating_type'],
            /* Important always have the country code as bellow */
            '_zipcode_country' => 'FRA',
        ];
    
    
        $repsonse = $this->vzlPushLead($lead_info, $app_base_url, $source_code, $activity_code);
        return $this->json([
            'message' => $repsonse
        ]);
    }

    private function vzlPushLead($data, $app_base_url = "https://boom.vezoa.com/", $source = null, $activity = null)
    {
        try {

            $vzl_route = trim($app_base_url, '/') . "/api/v1/leads";

            $vzl_ch = curl_init();

            $payload = json_encode(
                array(
                    "info" => $data,
                    "source" => $source,
                    "activity" => $activity,
                    "server" => $_SERVER,
                )
            );

            curl_setopt($vzl_ch, CURLOPT_URL, $vzl_route);
            curl_setopt($vzl_ch, CURLOPT_POST, 1);
            curl_setopt($vzl_ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($vzl_ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Accept:application/json'));
            curl_setopt($vzl_ch, CURLOPT_RETURNTRANSFER, true);

            $vzl_response = curl_exec($vzl_ch);

            /*
            |--------------------------------------------------------------------------
            |  If you want to save the lead in a log file to have a backup in the server, uncomment the next line
            |--------------------------------------------------------------------------
            */
            
            //file_put_contents("log.txt", $vzl_response . "\n \n", FILE_APPEND);

            curl_close($vzl_ch);

            return $vzl_response;

        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
