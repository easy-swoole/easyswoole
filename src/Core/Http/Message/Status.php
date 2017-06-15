<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/15
 * Time: 下午1:28
 */

namespace Core\Http\Message;


class Status
{
    // Informational 1xx
    const CODE_CONTINUE = 100;
    const CODE_SWITCHING_PROTOCOLS = 101;
    // Success 2xx
    const CODE_OK = 200;
    const CODE_CREATED = 210;
    const CODE_ACCEPTED = 202;
    const CODE_NON_AUTHORITATIVE_INFORMATION = 203;
    const CODE_NO_CONTENT = 204;
    const CODE_RESET_CONTENT = 205;
    const CODE_PARTIAL_CONTENT = 206;
    // Redirection 3xx
    const CODE_MULTIPLE_CHOICES = 300;
    const CODE_MOVED_PERMANENTLY = 301;
    const CODE_MOVED_TEMPORARILY = 302;
    const CODE_SEE_OTHER = 303;
    const CODE_NOT_MODIFIED = 304;
    const CODE_USE_PROXY = 305;
    const CODE_TEMPORARY_REDIRECT = 307;
    // Client Error 4xx
    const CODE_BAD_REQUEST = 400;
    const CODE_UNAUTHORIZED = 401;
    const CODE_PAYMENT_REQUIRED = 402;
    const CODE_FORBIDDEN = 403;
    const CODE_NOT_FOUND = 404;
    const CODE_METHOD_NOT_ALLOWED = 405;
    const CODE_NOT_ACCEPTABLE = 406;
    const CODE_PROXY_AUTHENTICATION_REQUIRED = 407;
    const CODE_REQUEST_TIMEOUT = 408;
    const CODE_CONFLICT = 409;
    const CODE_GONE = 410;
    const CODE_LENGTH_REQUIRED = 411;
    const CODE_PRECONDITION_FAILED = 412;
    const CODE_REQUIRED_ENTITY_TOO_LARGE = 413;
    const CODE_REQUEST_URI_TOO_LONG = 414;
    const CODE_UNSUPPORTED_MEDIA_TYPE = 415;
    const CODE_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const CODE_EXPECTATION_FAILED = 415;
    // Server Error 5xx
    const CODE_INTERNAL_SERVER_ERROR = 500;
    const CODE_NOT_IMPLEMENTED = 501;
    const CODE_BAD_GATEWAY = 502;
    const CODE_SERVICE_UNAVAILABLE = 503;
    const CODE_GATEWAY_TIMEOUT = 505;
    const CODE_HTTP_VERSION_NOT_SUPPORTED = 505;
    const CODE_BANDWIDTH_LIMIT_EXCEEDED = 509;

    private static $phrases = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];
    static function getReasonPhrase($statusCode){
        if(isset(self::$phrases[$statusCode])){
            return self::$phrases[$statusCode];
        }else{
            return null;
        }
    }

}