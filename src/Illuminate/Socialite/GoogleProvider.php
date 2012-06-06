<?php namespace Illuminate\Socialite;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\Response;
use Symfony\Component\HttpFoundation\Request;

class GoogleProvider extends Provider {

	/**
	 * The scope delimiter.
	 *
	 * @var string
	 */
	protected $scopeDelimiter = ' ';

	/**
	 * Get the auth end-point URL for a provider.
	 *
	 * @return string
	 */
	protected function getAuthEndpoint()
	{
		return 'https://accounts.google.com/o/oauth2/auth';
	}

	/**
	 * Get the access token end-point URL for a provider.
	 *
	 * @return string
	 */
	protected function getAccessEndpoint()
	{
		return 'https://accounts.google.com/o/oauth2/token';
	}

	/**
	 * Get the user data end-point URL for the provider.
	 *
	 * @return string
	 */
	protected function getUserDataEndpoint()
	{
		return 'https://www.googleapis.com/oauth2/v1/userinfo';
	}

	/**
	 * Get an array of query string options for a grant type.
	 *
	 * @param  Symfony\Component\HttpFoundation\Request
	 * @param  string  $grantType
	 * @param  array  $options
	 * @return array
	 */
	protected function getGrantTypeOptions(Request $request, $grantType, $options)
	{
		$return = array();

		// Here we will set the extra options needed for various grant type request.
		// This may be anything that is needed for a successful request and the
		// options we return will be merged into the rest of the parameters.
		if ($grantType == 'authorization_code')
		{
			$return['redirect_uri'] = $this->getCurrentUrl($request);
		}

		return $return;
	}

	/**
	 * Execute the request to get the access token.
	 *
	 * @param  Guzzle\Http\ClientInterface  $client
	 * @param  array  $options
	 * @return Guzzle\Http\Message\Response
	 */
	protected function executeAccessRequest(ClientInterface $client, $options)
	{
		return $client->post($this->getAccessEndpoint(), null, $options)->send();
	}

	/**
	 * Get an array of parameters from the access token response.
	 *
	 * @param  Guzzle\Http\Message\Response  $response
	 * @return array
	 */
	protected function parseAccessResponse(Response $response)
	{
		return $this->parseJsonResponse($response);
	}

	/**
	 * Create an access token with the given parameters.
	 *
	 * @param  array  $parameters
	 * @return AccessToken
	 */
	protected function createAccessToken(array $parameters)
	{
		return new AccessToken($parameters);
	}

	/**
	 * Get the user information using a token.
	 *
	 * @param  Illuminate\Socialite\AccessToken  $token
	 * @return UserData
	 */
	public function getUserData(AccessToken $token)
	{
		$query = http_build_query(array('access_token' => $token->getValue()));

		$response = $this->getHttpClient()->get($this->getUserDataEndpoint().'?'.$query)->send();

		return new UserData($this->parseJsonResponse($response));
	}

	/**
	 * Get the default scopes for the provider.
	 *
	 * @return array
	 */
	public function getDefaultScope()
	{
		$scopes[] = 'https://www.googleapis.com/auth/userinfo.profile';

		$scopes[] = 'https://www.googleapis.com/auth/userinfo.email';

		return $scopes;
	}

}