<?php include('header.php'); ?>

<style>
    .card {
        height: 200px; /* Set a fixed height for the cards */
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .card .header {
        flex: 0 0 auto; /* Ensure header doesn't stretch */
    }
    .card .container {
        flex: 1 1 auto; /* Allow container to take remaining space */
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card h1 {
        margin: 0; /* Remove default margin */
    }
</style>

<div class="row">
    <div class="col-sm-2">
        <?php include('sidebar.php'); ?>
    </div>

    <div class="col-sm-9">
        <h3>Dashboard</h3>
        <br />
        <br />
        <div class="row">
            <div class="col">
                <div class="card border-primary">
                    <div class="header bg-primary">
                        <h5><i class="fa fa-home" aria-hidden="true"></i> Boarding House</h5>
                    </div>
                    <div class="container text-center">
                        <?php
                        $result = mysqli_query($dbconnection, "SELECT count(1) FROM rental");
                        $row = mysqli_fetch_array($result);
                        $total_boarding_houses = $row[0];
                        echo "<h1>".$total_boarding_houses."</h1>";
                        ?>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card border-success">
                    <div class="header bg-success">
                        <h5><i class="fa fa-envelope" aria-hidden="true"></i> Requesting for Approval</h5>
                    </div>
                    <div class="container text-center">
                        <?php
                        $result = mysqli_query($dbconnection, "SELECT count(1) FROM landlords WHERE status=''");
                        $row = mysqli_fetch_array($result);
                        $total_requests = $row[0];
                        echo "<h1>".$total_requests."</h1>";
                        ?>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card border-danger">
                    <div class="header bg-danger">
                        <h5><i class="fa fa-thumbs-o-up" aria-hidden="true"></i> Approved Owners</h5>
                    </div>
                    <div class="container text-center">
                        <?php
                        $result = mysqli_query($dbconnection, "SELECT count(1) FROM landlords WHERE status='Approved'");
                        $row = mysqli_fetch_array($result);
                        $total_approved_owners = $row[0];
                        echo "<h1>".$total_approved_owners."</h1>";
                        ?>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card border-info">
                    <div class="header bg-info">
                        <h5><i class="fa fa-money" aria-hidden="true"></i> Total Monthly Income</h5>
                    </div>
                    <div class="container text-center">
                        <?php
                        $query = "
                            SELECT SUM(r.monthly) as total_income
                            FROM rental r
                            JOIN book b ON r.rental_id = b.bhouse_id
                            WHERE b.status = 'Approved'
                        ";
                        $resultincome = mysqli_query($dbconnection, $query);
                        if ($resultincome) {
                            $row = mysqli_fetch_array($resultincome);
                            $total_income = $row['total_income'];
                            echo "<h1>".number_format($total_income)."</h1>";
                        } else {
                            echo "Error: " . mysqli_error($dbconnection);
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <br />
        <br />

        <div class="row">
            <div class="col-md-6">
                <canvas id="incomeChart"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="brokerPieChart"></canvas>
            </div>
        </div>
        
        <div class="row">
            <div class="col">
                <canvas id="ratingChart"></canvas>
            </div>
        </div>

        <?php
        // Fetch monthly income for each boarding house including all houses
        $incomeQuery = "
            SELECT r.title as rental_name, IFNULL(SUM(r.monthly), 0) as total_income
            FROM rental r
            LEFT JOIN book b ON r.rental_id = b.bhouse_id AND b.status = 'Approved'
            GROUP BY r.title
        ";
        $incomeResult = mysqli_query($dbconnection, $incomeQuery);
        $rentalNames = [];
        $totalIncomes = [];

        if ($incomeResult) {
            while ($row = mysqli_fetch_assoc($incomeResult)) {
                $rentalNames[] = $row['rental_name'];
                $totalIncomes[] = $row['total_income'];
            }
        } else {
            echo "Error: " . mysqli_error($dbconnection);
        }

        // Fetch ratings for each boarding house and include only those with ratings greater than 0
        $ratingQuery = "
            SELECT r.title as rental_name, IFNULL(ROUND(AVG(NULLIF(b.ratings, 0)), 2), 0) as average_rating
            FROM rental r
            LEFT JOIN book b ON r.rental_id = b.bhouse_id
            GROUP BY r.title
        ";
        $ratingResult = mysqli_query($dbconnection, $ratingQuery);
        $rentalRatings = [];

        if ($ratingResult) {
            while ($row = mysqli_fetch_assoc($ratingResult)) {
                $rentalRatings[] = $row['average_rating'];
            }
        } else {
            echo "Error: " . mysqli_error($dbconnection);
        }

        // Fetch count of brokers for each boarding house
        $brokerQuery = "
            SELECT r.title as rental_name, COUNT(b.id) as broker_count
            FROM rental r
            LEFT JOIN book b ON r.rental_id = b.bhouse_id AND b.status = 'Approved'
            GROUP BY r.title
        ";
        $brokerResult = mysqli_query($dbconnection, $brokerQuery);
        $rentalBrokers = [];
        $totalBrokers = 0;

        if ($brokerResult) {
            while ($row = mysqli_fetch_assoc($brokerResult)) {
                $rentalBrokers[$row['rental_name']] = $row['broker_count'];
                $totalBrokers += $row['broker_count'];
            }
        } else {
            echo "Error: " . mysqli_error($dbconnection);
        }

        // Calculate percentages for brokers
        $brokerPercentages = [];
        foreach ($rentalBrokers as $rental => $brokers) {
            $percentage = ($brokers / $totalBrokers) * 100;
            $brokerPercentages[$rental] = round($percentage, 2);
        }
        ?>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var ctxIncome = document.getElementById('incomeChart').getContext('2d');
                var incomeChart = new Chart(ctxIncome, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($rentalNames); ?>,
                        datasets: [{
                            label: 'Boarding House Monthly Income',
                            data: <?php echo json_encode($totalIncomes); ?>,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                var ctxRating = document.getElementById('ratingChart').getContext('2d');
                var ratingChart = new Chart(ctxRating, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($rentalNames); ?>,
                        datasets: [{
                            label: 'Boarding House Ratings',
                            data: <?php echo json_encode($rentalRatings); ?>,
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 5 // Set max to 5 for ratings
                            }
                        }
                    }
                });

                var ctxBroker = document.getElementById('brokerPieChart').getContext('2d');
var brokerPieChart = new Chart(ctxBroker, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode(array_keys($brokerPercentages)); ?>,
        datasets: [{
            label: 'Brokers Percentage',
            data: <?php echo json_encode(array_values($brokerPercentages)); ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.5)',
                'rgba(54, 162, 235, 0.5)',
                'rgba(255, 206, 86, 0.5)',
                'rgba(75, 192, 192, 0.5)',
                'rgba(153, 102, 255, 0.5)',
                'rgba(255, 159, 64, 0.5)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return tooltipItem.label + ': ' + tooltipItem.raw.toFixed(2) + '%';
                    }
                }
            }
        }
    }
});

            });
        </script>

    </div>
</div>

<?php include('footer.php'); ?>
