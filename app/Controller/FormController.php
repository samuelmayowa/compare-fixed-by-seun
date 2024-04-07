<?php
namespace App\Controller;

use App\Service\CompanyMatcher;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use PDO;
use App\Controller\Controller;

class FormController extends Controller
{
    /**
     * Renders the home page.
     *
     * This method renders the home page template ('home.twig') and destroys the session.
     */
    public function home()
    {
        session_start();
        session_destroy();
        echo $this->render('home.twig');
    }

    /**
     * Renders the form page.
     *
     * This method renders the form template ('form.twig') and destroys the session.
     */
    public function index()
    {
        session_start();
        session_destroy();
        echo $this->render('form.twig');
    }

    /**
     * Handles the form submission.
     *
     * This method processes the submitted form data, matches companies using the CompanyMatcher service,
     * deducts credits, and renders the results template ('results.twig').
     */
    public function submit()
    {
        session_start();

        $request = $_REQUEST;
        //var_dump($request);
       //die();

        $matcher = new CompanyMatcher($this->db());
        $matcher->matchCompanies($request, 3); // Assuming you have a method to retrieve matched companies
        $results = $matcher->results();
        $totalRow = count($results);

        if (isset($_SESSION['postcode']) && isset($_SESSION['type']) && isset($_SESSION['bedrooms'])) {
            header("Location: search");
            if ($_SESSION['postcode'] == $request['postcode'] && $_SESSION['type'] == $request['type'] && $_SESSION['bedrooms'] == $request['bedrooms']) {
                // If the search has already been done, do not deduct credits again
                foreach ($results as $result) {
                    if ($result['credits'] <= 0) {
                        $this->logLowCredit($result['name'].', ');
                    }

                }
            } else {
                foreach ($results as $result) {
                    if ($result['credits'] <= 0) {
                        $this->logLowCredit($result['name'].', ');
                    } else {
                        $matcher->deductCredits($result['id']);
                    }

                }

                $_SESSION['postcode'] = $request['postcode'];
                $_SESSION['type'] = $request['type'];
                $_SESSION['bedrooms'] = $request['bedrooms'];
            }
        } else {
            foreach ($results as $result) {
                if ($result['credits'] <= 0) {
                    $this->logLowCredit($result['name'].', ');
                } else {
                    $matcher->deductCredits($result['id']);
                }
            }

            $_SESSION['postcode'] = $request['postcode'];
            $_SESSION['type'] = $request['type'];
            $_SESSION['bedrooms'] = $request['bedrooms'];
        }
//var_dump($results);die();
        echo $this->render('results.twig', [
            'matchedCompanies' => $results,
            'totalRow' => $totalRow
        ]);
    }

    /**
     * Logs low credit messages.
     *
     * This method logs the provided low credit message to the 'lowCreditLog.log' file.
     *
     * @param string $message The low credit message to be logged.
     */
    function logLowCredit($message)
    {
        $logFile = realpath(__DIR__ . '/../..') . "/public/lowCreditLog.log";

// Open the log file in append mode
        $logFile = fopen($logFile, 'a');

// Write the log message to the log file
        fwrite($logFile, $message . PHP_EOL);

// Close the log file handle
        fclose($logFile);
      
    }
}