protected function unauthenticated($request, AuthenticationException $exception)
{
    return response()->json(['message' => 'No autenticado.'], 401);
}
