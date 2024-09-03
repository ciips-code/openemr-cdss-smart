<?php

namespace OpenEMR\Module\CustomModuleCdss;

use Exception;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CdsssCommunicationService{
    protected $fhirResource;
    protected $url;
    private $client;
    protected $method;
    public function __construct($fhirResource = null,$url,$method) {
        $this->fhirResource = $fhirResource;
        $this->url = $url;
        $this->method = $method;
        $this->client = HttpClient::create();
    }

    public function sendRequest(){

        try{

            $response = $this->client->request($this->method,$this->url,[
            'body' => $this->fhirResource ?? '',
            'headers' => [
                'Content-type' => 'application/json',
                'Accept' => 'application/json'
                ],
            ]);

            return $response->getContent();

        }catch (ClientExceptionInterface $e) {
            
            throw new Exception('Client error: ' . $e->getMessage(),$e->getCode());
        } catch (ServerExceptionInterface $e) {

            throw new Exception('Server error: ' . $e->getMessage(), $e->getCode());
        } catch (RedirectionExceptionInterface $e) {

            throw new \Exception('Redirection error: ' . $e->getMessage(), $e->getCode());
        } catch (TransportExceptionInterface $e) {

            throw new \Exception('Transport error: ' . $e->getMessage(), $e->getCode());
        } catch (\Exception $e) {

            throw new \Exception('Error: ' . $e->getMessage(), $e->getCode());
        }

    }
        
}

