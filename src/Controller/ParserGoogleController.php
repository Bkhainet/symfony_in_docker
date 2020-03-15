<?php

namespace App\Controller;

use App\Entity\ParserGoogle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Knp\Component\Pager\PaginatorInterface;


class ParserGoogleController extends AbstractController
{

    public function SearchGoogle($word, $dom)
    {
        $fp = fopen ("parse.txt", "w");
        $cookiefile = 'cookie.txt';

        for ( $number = 0; $number < 100; $number=$number+10)
        {

            $URL_link = "http://www.google.ru/search?q=".rawurlencode($word)."&newwindow=1&client=opera&rls=ru&channel=suggest&ie=UTF-8&oe=UTF-8&ei=rQzxXZmvFJKvmwWl7LHADQ&start=".rawurlencode($number)."&sa=N";

            $ch = curl_init($URL_link);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

            curl_setopt ($ch, CURLOPT_FILE, $fp);

            sleep(2);

            $html = curl_exec($ch);

            $html = file_get_contents("parse.txt");
            preg_match_all('|<div class="BNeawe vvjwJb AP7Wnd">(.*?)</div>|', $html, $key_word);
            preg_match_all('|<div class="BNeawe UPmit AP7Wnd">(.*?)[\s<]|', $html, $domain);


            foreach (array_slice($key_word[1], 0, 10) as $key => $item_key_word)
            {
                $id_Google = $number + $key;
                $item_domain = array_slice($domain[1], 0, 10)[$key];

                $em = $this->getDoctrine()->getManager();
                $table = new ParserGoogle();

                $table->setIdGoogle($id_Google+1);
                $table->setDomaineName($item_domain);
                $table->setKeyWord($item_key_word);
                $table->setWord($word);
                $table->setCreatedAt(new \DateTime());

                $em->persist($table);
                $em->flush();
            }
        }
        fclose ($fp);
    }

    ///////////////////////////////////////////////////
//////////////////////////////////////////////////
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $table_Google = $em->getRepository(ParserGoogle::class)->findAll();
        if(!$table_Google)
        {
            $this->addFlash('success', 'Таблица пустая');
        }
        else{
            $this->addFlash('nav', 'История парсинга');
        }

        ///////////////////////
        $errorMessages = [];///
        ///////////////////////
        ///
        return $this->render('parser_google/index.html.twig', [
            'controller_name' => 'ParserGoogleController',
            'table_Google' => $table_Google,
            'errorMessages' => $errorMessages,
        ]);
    }

    public function post(Request $request,  ValidatorInterface $validator)
    {
        $word = $request->get('key_word');
        $dom = $request->get('key_domaine');

        $input = ['key_word' => $word, 'key_domaine' => $dom];
        $constraints = new Assert\Collection([
            'key_word' => [new Assert\Length(['min' => 2]), new Assert\NotBlank],
            'key_domaine' => [new Assert\Length(['min' => 2]), new Assert\notBlank]
        ]);
        $violations = $validator->validate($input, $constraints);
        if (count($violations) > 0)
        {
            $accessor = PropertyAccess::createPropertyAccessor();

            $errorMessages = [];

            foreach ($violations as $violation)
            {
                $accessor->setValue($errorMessages,
                    $violation->getPropertyPath(),
                    $violation->getMessage());
            }
            return $this->render('parser_google/index.html.twig',
                ['errorMessages' => $errorMessages]);
        }

        $this->SearchGoogle($word, $dom);

        $em = $this->getDoctrine()->getManager();

        $search = $em->getRepository(ParserGoogle::class)->findBy(['domaine_name' => $dom]);

        if(!$search)
        {
            $this->addFlash('success', 'Домен не найден');
        }
        else{
            $this->addFlash('tables', 'История парсинга');
        }
        return $this->render('parser_google/key.html.twig', [
            'controller_name' => 'ParserGoogleController',
            'search' =>  $search,
        ]);
    }

    public function show_history(Request $request, PaginatorInterface $paginator)
    {
        $em = $this->getDoctrine()->getManager();
        $table_Google = $em->getRepository(ParserGoogle::class);

        $allAppointmentsQuery = $table_Google->createQueryBuilder('p');

        $appointments = $paginator->paginate(
            $allAppointmentsQuery,
            $request->query->getInt('page', 1), 10);

        return $this->render('parser_google/history.html.twig', [
            'controller_name' => 'ParserGoogleController',
            'appointments' => $appointments,
            'table_Google' => $table_Google
        ]);
    }
    
    public function delete_show_history()
    {
        $em = $this->getDoctrine()->getManager();
        $table_Google = 'TRUNCATE TABLE parser_google;';

        $connection = $em->getConnection();
        $stmt = $connection->prepare($table_Google);
        $stmt->execute();
        $stmt->closeCursor();

        $errorMessages = [];

        return $this->render('parser_google/index.html.twig',
        ['errorMessages' => $errorMessages]); 
    }
}
