<?php
/**
 * This Software is part of aryelgois/bank-interchange and is provided "as is".
 *
 * @see LICENSE
 */

namespace aryelgois\BankInterchange\ReturnFile;

use aryelgois\BankInterchange\Models;
use aryelgois\MedoolsRouter;

/**
 * A Router class to add a custom return file endpoint
 *
 * @author Aryel Mota Góis
 * @license MIT
 * @link https://www.github.com/aryelgois/bank-interchange
 */
class Router extends MedoolsRouter\Router
{
    /**
     * Disables X-Http-Method-Override header
     *
     * @const boolean
     */
    const ENABLE_METHOD_OVERRIDE = false;

    /**
     * List of supported Request payload content types
     *
     * @const string[]
     */
    const SUPPORTED_CONTENT_TYPES = [
        'text/plain',
    ];

    /**
     * Map of available Response content types
     *
     * Order: Prefered first
     *
     * @const string[]
     */
    const AVAILABLE_CONTENT_TYPES = [
        'extracted' => 'application/x.bank-interchange.return-file_extracted+json',
        'parsed' => 'application/x.bank-interchange.return-file_parsed+json',
    ];

    /**
     * When requested route is '/'
     *
     * @param array  $headers Request Headers
     * @param string $body    Request Body
     *
     * @return Response With parsed or extracted Return File
     */
    protected function requestRoot(array $headers, string $body)
    {
        if ($this->method !== 'POST') {
            $this->sendError(
                static::ERROR_METHOD_NOT_IMPLEMENTED,
                "Method '$this->method' is not implemented",
                'POST'
            );
        }

        $content_type = $headers['Content-Type'] ?? '';
        $content_type = static::parseContentType($content_type);
        $mime = $content_type['mime'] ?? $content_type;

        if (empty($mime) || empty(trim($body))) {
            $this->sendError(
                static::ERROR_INVALID_PAYLOAD,
                'Expecting payload'
            );
        }

        if (!in_array($mime, static::SUPPORTED_CONTENT_TYPES)) {
            $this->sendError(
                static::ERROR_UNSUPPORTED_MEDIA_TYPE,
                "Media-Type '$mime' is not supported"
            );
        }

        if (($charset = $content_type['charset'] ?? null) !== null) {
            $body = mb_convert_encoding($body, 'UTF-8', $charset);
        }

        $available = static::AVAILABLE_CONTENT_TYPES;
        $accepted = static::getAcceptedType(
            $headers['Accept'] ?? '*/*',
            $available
        );
        if ($accepted === false) {
            $message = 'Can not generate content complying to Accept header';
            $this->sendError(static::ERROR_NOT_ACCEPTABLE, $message);
        }

        try {
            $return_file = new Parser($body);
        } catch (\InvalidArgumentException $e) {
            $code = static::ERROR_INVALID_PAYLOAD;
        } catch (\DomainException $e) {
            $code = static::ERROR_INVALID_PAYLOAD;
        } catch (ParseException $e) {
            $code = static::ERROR_INVALID_PAYLOAD;
        } catch (\UnexpectedValueException $e) {
            $code = static::ERROR_INVALID_PAYLOAD;
        } catch (\Exception $e) {
            $code = static::ERROR_INTERNAL_SERVER;
        } finally {
            if (isset($code)) {
                $this->sendError($code, $e->getMessage());
            }
        }

        if ($accepted === $available['extracted']) {
            try {
                $extractor = $return_file->extract();
            } catch (\Exception $e) {
                $this->sendError(
                    static::ERROR_INTERNAL_SERVER,
                    $e->getMessage()
                );
            }

            $data = $extractor->output();

            $authorization = $this->getAuthorizedResources('GET');
            $assignments = (array_key_exists('assignments', $authorization))
                ? Models\Assignment::dump($authorization['assignments'], 'id')
                : [];
            $titles = (array_key_exists('titles', $authorization))
                ? Models\Title::dump($authorization['titles'], 'id')
                : [];

            foreach ($data['titles'] as &$title) {
                if (!in_array($title['assignment'], $assignments)) {
                    $title['assignment'] = null;
                }
                if (!in_array($title['id'], $titles)) {
                    $title['id'] = null;
                }
            }
            unset($title);
        } else {
            $data = $return_file->output();
        }

        $response = $this->prepareResponse();
        $response->headers['Content-Type'] = $accepted;
        $response->body = $data;

        return $response;
    }
}
