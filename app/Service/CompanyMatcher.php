<?php

namespace App\Service;

use PDO;

class CompanyMatcher
{
    private $db;
    private $matches = [];

    /**
     * CompanyMatcher constructor.
     *
     * @param PDO $db The PDO database connection.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Matches companies based on the provided search criteria.
     *
     * This method performs a database query to match companies based on the search criteria
     * provided in the `$request` parameter. The matching rows are stored in the `$matches` property.
     *
     * @param array $request The search criteria.
     * @param int $limit The maximum number of matched companies to retrieve.
     */
    public function matchCompanies($request, $limit = 3)
    {
        $postCode = $this->extractPostcodePattern($request['postcode']);

        // Prepare the SQL query to fetch all matching rows
        $query = "SELECT companies.* FROM company_matching_settings
              INNER JOIN companies ON companies.id = company_matching_settings.company_id
              WHERE postcodes LIKE :postcode AND bedrooms LIKE :bedroom AND type LIKE :typed";

        $stmt = $this->db->prepare($query);

        // Bind the search value parameters with wildcard characters
        $stmt->bindValue(':postcode', '["' . $postCode . '"]', PDO::PARAM_STR);
        $stmt->bindValue(':bedroom', '%"'.$request['bedrooms'].'"%', PDO::PARAM_STR);
        $stmt->bindValue(':typed', $request['type'], PDO::PARAM_STR);

        // Execute the prepared statement
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Randomly shuffle the array of rows
        shuffle($rows);

        // Select the first three rows from the shuffled array
        $selectedRows = array_slice($rows, 0, $limit);

        foreach ($selectedRows as $row) {
            $this->matches[] = $row;
        }
    }

    /**
     * Retrieves the matched companies.
     *
     * This method returns an array of companies that have been matched.
     *
     * @return array The array of matched companies.
     */
    public function results(): array
    {
        return $this->matches;
    }

    /**
     * Deducts credits from a company.
     *
     * This method deducts one credit from the company with the specified ID.
     *
     * @param int $id The ID of the company.
     */
    public function deductCredits($id)
    {
        $query = "UPDATE companies SET credits = credits - 1 WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);

        try {
            $stmt->execute();
            // echo 'Record updated successfully.';
        } catch (PDOException $e) {
            // echo 'Error updating record: ' . $e->getMessage();
        }
    }

    /**
     * Extracts the postcode pattern.
     *
     * This method extracts the postcode pattern from the provided postcode string.
     *
     * @param string $postcode The postcode string.
     * @return string|null The extracted postcode pattern or null if no pattern match.
     */
    function extractPostcodePattern($postcode)
    {
        $pattern1 = '/^([A-Za-z]{2})\d+/';  // Two letters followed by numbers
        $pattern2 = '/^([A-Za-z])\d+/';     // One letter followed by numbers

        if (preg_match($pattern1, $postcode, $matches)) {
            return $matches[1];  // Return the first two letters
        } elseif (preg_match($pattern2, $postcode, $matches)) {
            return $matches[1];  // Return the first letter
        }

        return null;  // Return null if no pattern match
    }
}