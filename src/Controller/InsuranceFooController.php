<?php

namespace App\Controller;

use App\Application\Dto\InputParameterDTO;
use App\Application\UseCase\CreateInsuranceXML;
use App\Infrastruture\FooInsuranceClient;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InsuranceFooController extends AbstractBaseController
{
    public function __construct(
        CreateInsuranceXML $createInsuranceXML,
        ValidatorInterface $validator,
        private readonly FooInsuranceClient $fooInsurance
    ) {
        parent::__construct($createInsuranceXML, $validator);
    }

    public function showXML(Request $request): Response|JsonResponse
    {
        try {
            $parameters = $this->parseRequestParameters(json_decode($request->getContent(), true));
            $violations = $this->validator->validate($parameters);

            if (count($violations) > 0) {
                throw new BadRequestException($this->formatValidationErrors($violations));
            }

            $xmlContent = $this->createInsuranceXML->createXML($parameters, $this->fooInsurance);

            return new Response($xmlContent, Response::HTTP_CREATED, [
                'Content-Type' => 'application/xml'
            ]);
        } catch (BadRequestException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $exception) {
            return new JsonResponse(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

