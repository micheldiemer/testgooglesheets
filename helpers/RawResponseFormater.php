<?php

namespace app\helpers;

use \yii\web\ResponseFormatterInterface;


/**
 * Formatage d’une réponse http
 */
class RawResponseFormater implements ResponseFormatterInterface
{
    /**
     * ajoute des en-têtes de texte brut à une [Réponse http]
     *
     * @param  \yii\web\Response $response
     * @return void
     */
    public function format($response)
    {
        $response->format = \yii\web\Response::FORMAT_RAW;
        $response->headers->add('Content-Type', 'text/plain; charset=utf-8');
    }

    /**
     * Retourne une réponse en texte brut
     *
     * @param \yii\web\Response $response
     * @param  string $text
     * @param  int cpde
     * @return yii\web\Response
     */
    public static function rawResponse($response, string $text, int $statusCode = 200): \yii\web\Response
    {
        (new RawResponseFormater)->format($response);
        $response->setStatusCode($statusCode);
        $response->data = $text;
        return $response;
    }
}
