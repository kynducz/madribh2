<?php include('header.php'); ?>

<?php
if (isset($_POST["delete"])) {
    $id = $_POST['rowid'];

    $sql = "DELETE FROM rental WHERE id=?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Record Deleted Successfully');</script>";
    } else {
        echo "Error Deleting record: " . $stmt->error;
    }

    $stmt->close();
}
?>

<div class="row">
<div class="col-sm-2">
    <?php include('sidebar.php'); ?>
</div>

<div class="col-sm-9">
    <br />
    <h3>Boarding House List</h3>
    <br />
    <br />

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Vacant</th>
                <th>Occupied</th>
                <th>View</th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>

        <?php
        if (isset($_GET['pageno'])) {
            $pageno = $_GET['pageno'];
        } else {
            $pageno = 1;
        }

        $no_of_records_per_page = 8;
        $offset = ($pageno - 1) * $no_of_records_per_page;

        $total_pages_sql = "SELECT COUNT(*) FROM landlords";
        $result_pages = mysqli_query($dbconnection, $total_pages_sql);
        $total_rows = mysqli_fetch_array($result_pages)[0];
        $total_pages = ceil($total_rows / $no_of_records_per_page);

        $sql = "SELECT * FROM rental WHERE landlord_id=? LIMIT ?, ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("iii", $login_session, $offset, $no_of_records_per_page);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $rent_id = $row['rental_id'];
        ?>

            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td class="col-md-1">
                    <?php 
                    $sql_book = "SELECT COUNT(*) FROM book WHERE bhouse_id=? AND landlord_id=? AND status='Approved'";
                    $stmt_book = $dbconnection->prepare($sql_book);
                    $stmt_book->bind_param("ii", $rent_id, $login_session);
                    $stmt_book->execute();
                    $result_book = $stmt_book->get_result();
                    $occupied = $result_book->fetch_array()[0];
                    $stmt_book->close();

                    $vacant_slots = $row['slots'] - $occupied;
                    echo htmlspecialchars($vacant_slots); 
                    ?>
                </td>
                <td class="col-md-1"><?php echo htmlspecialchars($occupied); ?></td>
                <td class="col-md-1"><a href="../view.php?bh_id=<?php echo htmlspecialchars($rent_id); ?>" class="btn btn-success"><i class="fa fa-eye" aria-hidden="true"></i></a></td>
                <td class="col-md-1"><a href="edit.php?bh_id=<?php echo htmlspecialchars($rent_id); ?>" class="btn btn-warning"><i class="fa fa-pencil-square" aria-hidden="true"></i></a></td>
                <td class="col-md-1">
                    <form action="" method="POST">
                        <input type="hidden" name="rowid" value="<?php echo htmlspecialchars($row['id']); ?>">
                        <button type="submit" name="delete" class="btn btn-danger delete"><i class="fa fa-trash" aria-hidden="true"></i></button>
                    </form>
                </td>
            </tr>

        <?php 
        } 
        $stmt->close();
        ?>

        </tbody>
    </table>
    <ul class="pagination">
        <li><a href="?pageno=1"><i class="fa fa-fast-backward" aria-hidden="true"></i> First</a></li>
        <li class="<?php if($pageno <= 1){ echo 'disabled'; } ?>">
            <a href="<?php if($pageno <= 1){ echo '#'; } else { echo "?pageno=".($pageno - 1); } ?>"><i class="fa fa-chevron-left" aria-hidden="true"></i> Prev</a>
        </li>
        <li class="<?php if($pageno >= $total_pages){ echo 'disabled'; } ?>">
            <a href="<?php if($pageno >= $total_pages){ echo '#'; } else { echo "?pageno=".($pageno + 1); } ?>">Next <i class="fa fa-chevron-right" aria-hidden="true"></i></a>
        </li>
        <li><a href="?pageno=<?php echo $total_pages; ?>">Last <i class="fa fa-fast-forward" aria-hidden="true"></i></a></li>
    </ul>

</div>
</div>

<?php include('footer.php'); ?>
