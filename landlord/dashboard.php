<?php include('header.php'); ?>

<?php 
// Initialize arrays
$boarding_houses = [];
$monthly_incomes = [];
$total_income = 0;

// Query to fetch boarding houses and their monthly incomes for the current landlord
$query = "
    SELECT r.title as boarding_house, SUM(r.monthly) as monthly_income
    FROM rental r
    JOIN book b ON r.rental_id = b.bhouse_id
    WHERE r.landlord_id = '$login_session' AND b.status = 'Approved'
    GROUP BY r.title
";

$result = mysqli_query($dbconnection, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $boarding_houses[] = $row['boarding_house'];
        $monthly_incomes[] = $row['monthly_income'];
    }
} else {
    echo "Error: " . mysqli_error($dbconnection);
}

// Query to fetch brokers count for each boarding house
$brokers_query = "
    SELECT r.title as boarding_house, COUNT(b.id) as broker_count
    FROM rental r
    JOIN book b ON r.rental_id = b.bhouse_id
    WHERE r.landlord_id = '$login_session' AND b.status = 'Approved'
    GROUP BY r.title
";

$brokers_result = mysqli_query($dbconnection, $brokers_query);

$brokers_data = [];
$total_brokers = 0;

if ($brokers_result) {
    while ($row = mysqli_fetch_assoc($brokers_result)) {
        $brokers_data[] = $row;
        $total_brokers += $row['broker_count'];
    }
} else {
    echo "Error: " . mysqli_error($dbconnection);
}

$broker_labels = array_column($brokers_data, 'boarding_house');
$broker_counts = array_column($brokers_data, 'broker_count');
$broker_percentages = [];

foreach ($broker_counts as $count) {
    $broker_percentages[] = ($count / $total_brokers) * 100;
}
?>

<div class="row">
    <div class="col-sm-2">
        <?php include('sidebar.php'); ?>
    </div>

    <div class="col-sm-9">
        <br />
        <h3>Dashboard</h3>
        <br />
        <br />
        <div class="row">
            <!-- Other cards -->
        </div>

        <br />

        <div class="col-md-12">
            <!-- Bar Chart for Monthly Incomes of Boarding Houses -->
            <canvas id="monthlyIncomeChart" style="width:100%;"></canvas>
        </div>

        <br />

        <div class="col-md-12">
            <!-- Pie Chart for Brokers Percentage -->
            <canvas id="brokerPieChart" style="width:100%;"></canvas>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById('monthlyIncomeChart').getContext('2d');
        var monthlyIncomeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($boarding_houses); ?>,
                datasets: [{
                    label: 'Monthly Income',
                    data: <?php echo json_encode($monthly_incomes); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Income ($)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Boarding House'
                        }
                    }
                }
            }
        });

        // Pie Chart for Brokers Percentage
        var ctxBroker = document.getElementById('brokerPieChart').getContext('2d');
        var brokerPieChart = new Chart(ctxBroker, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($broker_labels); ?>,
                datasets: [{
                    label: 'Brokers Percentage',
                    data: <?php echo json_encode($broker_percentages); ?>,
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
