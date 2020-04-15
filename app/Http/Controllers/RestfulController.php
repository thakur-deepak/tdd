<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as IlluminateResponse;

class RestfulController extends Controller
{
    /**
     * Normal status response code
     *
     * @var integer
     */
    private $statusCode = IlluminateResponse::HTTP_OK;

    /**
     * Headers to send
     *
     * @var array
     */
    private $headers = [];

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Sets the status code response
     *
     * @param integer $statusCode
     * @return \Http\Controllers\RestfulController
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Sets the headers for the response
     *
     * @param array $headers
     * @return \Http\Controllers\RestfulController
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Returns the current status code
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Respond to an api call with not found error
     *
     * @param string $message
     * @param array $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondNotFound($message = 'Not found', $errors = [])
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)->respondWithError($message, $errors);
    }

    /**
     * Use it to respond when an entity has been created. It sets the http status code to 201: Created
     *
     * @param array $data 'data' key contains the created entity. 'message' key contains the successfuly message to inform the user.
     * @return string json response
     */
    public function respondCreated($data = 'Resource created successfully')
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_CREATED)->respond($data);
    }

    /**
     * Respond to the API call with unauthorized error
     *
     * @param string $message
     * @param array $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondUnauthorized($message = 'Unauthorized', $errors = [])
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_UNAUTHORIZED)->respondWithError($message, $errors);
    }

    /**
     * Respond to the API call with status 403
     *
     * @param string $message
     * @param array $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondForbidden($message = 'Forbidden', $errors = [])
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_FORBIDDEN)->respondWithError($message, $errors);
    }

    /**
     * Respond to the API call with validation failed
     *
     * @param string $message
     * @param array $errors
     * @param integer $errorCode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondValidationFailed($message = 'Validation Failed', $errors = [], $errorCode = null)
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError($message, $errors, $errorCode);
    }

    /**
     * Respond to the API call with Unprocesable Entry
     *
     * @param mixed $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondUnprocesableEntry($data)
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respond($data);
    }

    /**
     * Respond to the API call with bad request error
     *
     * @param string $message
     * @param array $errors
     * @param integer $errorCode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondBadRequest($message = 'Validation Failed', $errors = [], $errorCode = null)
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_BAD_REQUEST)->respondWithError($message, $errors, $errorCode);
    }

    /**
     * Responds with an error 500 when an error has ocurred
     *
     * @param string $message
     * @param array $errors
     * @param integer $errorCode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondError($message = 'An internal error has occurre', $errors = [], $errorCode = null)
    {
        return $this->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)->respondWithError($message, $errors, $errorCode);
    }

    /**
     * Sends a response
     *
     * @param mixed $data
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respond($data, $headers = [])
    {


        $this->setHeaders($headers); // Buscar una forma mas elegante para hacer esto..

        return \Response::json($data, $this->getStatusCode(), $this->headers); // remover el uso de FACADES
    }

    /**
     * Respond to an API call with a paginated response
     *
     * @param LengthAwarePaginator $paginator
     * @param mixed $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondWithPagination(LengthAwarePaginator $paginator, $data)
    {
        $paginated_data = array_merge($data, [
            'paginator' => [
                'pages' => ceil($paginator->total() / $paginator->perPage()),
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        ]);
        return $this->respond($paginated_data);
    }

    /**
     * Respond to an API call with a downloable csv file
     *
     * @param array $columnas
     * @param array $data
     * @param string $filename
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondDownloableCSV(array $columnas, array $data, $filename = 'export.csv', $headers = [])
    {
        $csv = implode(";", $columnas) . "\n";
        foreach ($data as $row) {
            $csv .= implode(';', $row) . "\n";
        }
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename='{$filename}'"
        ];
        return Response::make($csv, $this->getStatusCode(), $headers);
    }

    /**
     * Generates a response with error codes
     *
     * @param string $message
     * @param array $errors
     * @param integer $errorCode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function respondWithError($message, $errors = [], $errorCode = null)
    {
        $errors = empty($errors) ? $errors : ['errors' => $errors];
        $errorCode = empty($errorCode) ? $this->getStatusCode() : $errorCode;
        return $this->respond(array_merge([
            'message' => $message,
            'status_code' => $this->getStatusCode(),
            'code' => $errorCode
        ], $errors));
    }
}

