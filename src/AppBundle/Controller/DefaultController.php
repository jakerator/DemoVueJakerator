<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
class DefaultController extends Controller
{
    /**
     * Default controller for Homepage
     *
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('default/index_lte.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ));
    }


    /**
     *
     * REST API controller for getting JSON array of patients, based on filter criterias
     *
     * @Route("/ds/patients", name="getPatients")
     */
    public function getPatientsAction(Request $request)
    {
        /* Input parameters         */
        $filterGroup=$request->query->get('filterGroup');
        $filterText=$request->query->get('filterText');
        $filterStatus=$request->query->get('filterStatus');
        $offset=$request->query->get('offset',0);
        /* default 'items per page' limit is set to 5 , but can be overriden by front-end call */
        $limit=$request->query->get('limit',5);


        /*  Getting source JSON as array      */
        $json_array=\AppBundle\JsonDataProvider::getJson();

        /* fetching full patients list from whole array */
        $patients=$json_array['collections']['patient'];

        /* filter list if any of filters used */
        if (strlen($filterStatus) or strlen($filterText) or strlen($filterGroup))
        {
             $patients=\AppBundle\JsonDataProvider::filterPatients($patients,$filterStatus,$filterText,$filterGroup);
        }

        /* Sort results alphabetically */
        $patients=\AppBundle\JsonDataProvider::sortPatients($patients);

        /* begin pagination logic */
        $countResultPatients=count($patients)-$offset;
        $responseArray=[];

        $patients=array_slice($patients,$offset,$limit);

        if ($countResultPatients>$limit)
        {
            $responseArray['nextOffset']=$offset+$limit;
        }
        /* end pagination logic */

        $responseArray['patients']=$patients;
        $responseArray['limit']=$limit;

        $response = new JsonResponse($responseArray);

        /* returning final JSON response with patients list and service pagination data */
        return $response;


    }


    /**
     *
     * REST API controller for getting JSON array of groups
     *
     * @Route("/ds/groups", name="getGroups")
     */
    public function getGroups(Request $request)
    {
        /*  Getting source JSON as array      */
        $json_array = \AppBundle\JsonDataProvider::getJson();

        /* fetching full patients list from whole array */
        $patients = $json_array['collections']['patient'];

        /* creating groups array from active patients only */
        $groups=[];
        foreach ((array)$patients as $patient)
        {
            if ( (string)$patient['status']=='1')
            {
                $groups[$patient['group']]=$patient['group'];
            }

        }
        $groups=array_keys($groups);

        /* returning final JSON response with groups list */
        $response = new JsonResponse(['groups'=>$groups]);
        return $response;


    }


    /**
     *
     * Sample route to Patient page, that tied to FOSJsRoutingBundle
     * This route is used in every 'patient-item' Vue.js component
     *
     * @Route("/ds/showPatient/{id}", options={"expose"=true}, name="showPatient")
     */
    public function showPatient($id)
    {

        /*
         * Here can be some logic to get Patient details to show on this page. But as this is just a demo, we're showing it's ID only
         */

        return $this->render('default/index_lte.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
            'patient_id'=>$id,
        ));

    }




}
