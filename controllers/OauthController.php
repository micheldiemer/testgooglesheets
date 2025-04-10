<?php

namespace app\controllers;

use app\helpers\RawResponseFormater;
use yii\web\Controller;
use yii\web\HttpException;
use \app\helpers\Utils;
use yii\helpers\Url;
use yii\web\Response;

/**
 * OauthController
 * 1. App->Google   Request token
 * 2. User->Google  User logs in and gives consent
 * 3. Google->App   Authorization code is provided as GET parameter
 * 4. App->Google   Exchange code for token
 * 5. Google->App   Google sends token
 * 6. App->Google   Send token to consume api
 *
 */
class OauthController extends Controller
{
    private $client;
    private $service;

    private const ACCESS_TOKEN = 'access_token';
    private const OAUTH_TOKEN_STATE = 'oauth_token_state';
    private const OAUTH_TOKEN_URI = 'oauth_token_received_redirect_uri';
    private const CLIENT_INIT = 0;
    private const REQUEST_TOKEN = 1;
    private const GOOGLE_SENT_CODE = 3;
    private const TOKEN_RECEIVED = 5;


    // region méthodes privées

    /**
     * Initialize le client Google stocké dans $this->client
     *
     * @return void
     */
    private function initClient()
    {
        // Initialisation du client Google (API Google)
        $this->client = new \Google_Client();
        // compléter

        //$this->client->setAccessToken( ...
    }

    /**
     * Effectue la redirection vers l'url d'autorisation de Google
     *
     * @return void
     */
    private function redirectAuthUrl()
    {
        $auth_url = $this->client->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    }

    /**
     * Avec le code d'autorisation, le client nous fournit un token d'accès
     * On met à jour la session, on a un token d'accès valide
     *
     * @param string $code
     * @return void
     */
    private function exchangeCodeForToken(string $code)
    {
        $this->client->fetchAccessTokenWithAuthCode($code);
        $_SESSION[self::OAUTH_TOKEN_STATE] = self::TOKEN_RECEIVED;
        $_SESSION[self::ACCESS_TOKEN] = $this->client->getAccessToken();
    }

    /**
     * Vérifie si le token d'accès a été réceptionné et s'il est valide
     * On utilise notre état interne ainsi que le client Google
     * @return bool
     */
    private function isTokenValid(): bool
    {
        // compléter
        return false;
    }

    /**
     * À partir du token d'accès, on configure le service Google
     * Le service Google nous permettra de manipuler les Google Sheets
     *
     * @return void
     */
    private function setServiceFromToken()
    {
        if (!$this->isTokenValid()):
            throw new \Exception('Access token not set');
        endif;

        if (is_null($this->client)) $this->initClient();
        $this->client->setAccessToken($this->client->getAccessToken());

        #// configure the Sheets Service
        $this->service = new \Google_Service_Sheets($this->client);
    }
    // endregion

    // region méthodes publiques

    /**
     * actionRequestToken
     * 1. App->Google   Request token
     * 2. User->Google  User logs in and gives consent
     *
     * @return void
     */
    public function actionRequestToken()
    {
        // compléter
    }

    /**
     * getCode
     * 3. Google->App   Authorization code is provided as GET parameter
     * 4. App->Google   Exchange code for token
     *
     */
    public function actionGetcode()
    {
        // compléter
    }
    // endregion

    // region Google Sheets

    /**
     * getValues
     *
     * @param  string $range Notation A1 ou R1C1 de la plage à partir de laquelle récupérer les valeurs.
     * @param  enum (Dimension) $dim majorDimension
     * @param  enum (ValueRenderOption) $r valueRenderOption
     * @param  enum (DateTimeRenderOption) $dt dateTimeRenderOption
     * @return array|Response|null
     */
    public function getValues(string $uri,  string $range, $dim = '', string $r = '', string $dt = ''): array|Response|null
    {

        $_SESSION[self::OAUTH_TOKEN_URI] = $uri;

        $r = \in_array($r, ['FORMATTED_VALUE', 'UNFORMATTED_VALUE', 'FORMULA']) ? $r : 'FORMATTED_VALUE';
        $dim = \in_array($dim, ['ROWS', 'COLUMNS']) ? $dim : 'COLUMNS';
        $dt =  \in_array($dim, ['SERIAL_NUMBER', 'FORMATTED_STRING']) ? $dim : 'SERIAL_NUMBER';

        if (!$this->isTokenValid()):
            return $this->response->redirect(Url::to(['oauth/request-token']));
        endif;

        $this->setServiceFromToken();

        $spreadsheetId = \Yii::$app->params['spreadsheetId'];

        return $this->service->spreadsheets_values->get(
            $spreadsheetId,
            $range,
            [
                'majorDimension' => $dim,
                'valueRenderOption' => $r,
                'dateTimeRenderOption' => $dt
            ]
        )->getValues();
    }

    public function updateValues(string $range, array $values, string $valueInputOption = 'USER_ENTERED')
    {
        /* Load pre-authorized user credentials from the environment.
            TODO(developer) - See https://developers.google.com/identity for
            guides on implementing OAuth2 for your application. */
        if (!$this->isTokenValid()):
            return $this->response->redirect(Url::to(['oauth/request-token']));
        endif;

        $this->setServiceFromToken();

        //$valueInputOption = \in_array($valueInputOption, ['INPUT_VALUE_OPTION_UNSPECIFIED', 'RAW', 'USER_ENTERED']) ? $valueInputOption : 'INPUT_VALUE_OPTION_UNSPECIFIED';
        $valueInputOption = \in_array($valueInputOption, ['RAW', 'USER_ENTERED']) ? $valueInputOption : 'USER_ENTERED';

        $spreadsheetId = $spreadsheetId = \Yii::$app->params['spreadsheetId'];;

        $body = new \Google_Service_Sheets_ValueRange([
            'values' => [$values]
        ]);
        $params = [
            'valueInputOption' => $valueInputOption
        ];

        try {
            //executing the request
            $result = $this->service->spreadsheets_values->update(
                $spreadsheetId,
                $range,
                $body,
                $params
            );
            return $result;
        } catch (\Exception $e) {
            // gérer l'exception:que
        }
    }

    public function getFirstRow(string $uri, string $type, string $col = 'Z'): array|Response
    {
        return $this->getValues($uri, $type, "A1:{$col}1");
    }
    // end region
}
