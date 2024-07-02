<?php include('header.php'); ?>

<?php
if (isset($_POST["approve"])) {
    $id = $_POST['rowid'];
    $sql_approve = "UPDATE landlords SET status='Approved' WHERE id=?";
    $stmt = $dbconnection->prepare($sql_approve);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo '<script type="text/javascript">alert("Successfully Approved");</script>';
    } else {
        echo '<script type="text/javascript">alert("Error in database.");</script>';
    }
    
    $stmt->close();
}

if (isset($_POST["delete"])) {
    $id = $_POST['rowid'];
    $sql_delete = "DELETE FROM landlords WHERE id=?";
    $stmt = $dbconnection->prepare($sql_delete);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo '<script type="text/javascript">alert("Successfully Deleted");</script>';
    } else {
        echo '<script type="text/javascript">alert("Error in database.");</script>';
    }
    
    $stmt->close();
}
?>

<div class="row">
    <div class="col-sm-2">
        <?php include('sidebar.php'); ?>
    </div>
    <div class="col-sm-8">
        <h3>Pending for Approval</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Contact Number</th>
                    <th>Facebook</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $pageno = isset($_GET['pageno']) ? $_GET['pageno'] : 1;
                $no_of_records_per_page = 8;
                $offset = ($pageno - 1) * $no_of_records_per_page;

                $sql_count = "SELECT COUNT(*) FROM landlords WHERE status=''";
                $result_count = mysqli_query($dbconnection, $sql_count);
                $total_rows = mysqli_fetch_array($result_count)[0];
                $total_pages = ceil($total_rows / $no_of_records_per_page);

                $sql_fetch = "SELECT * FROM landlords WHERE status='' LIMIT ?, ?";
                $stmt = $dbconnection->prepare($sql_fetch);
                $stmt->bind_param("ii", $offset, $no_of_records_per_page);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                ?>
                    <tr>
                        <td><img src="../uploads/<?php echo $row['profile_photo']; ?>" alt="Profile Photo" width="50" height="50"></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['Address']; ?></td>
                        <td><?php echo $row['contact_number']; ?></td>
                        <td><?php echo $row['facebook']; ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <form action="" method="POST" style="display:inline;">
                                    <input type="hidden" name="rowid" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="approve" class="btn btn-primary">APPROVE</button>
                                </form>
                                <form action="" method="POST" style="display:inline; margin-left: 5px;">
                                    <input type="hidden" name="rowid" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-danger">
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php } 
                $stmt->close();
                ?>
            </tbody>
        </table>
        <ul class="pagination">
            <li><a href="?pageno=1"><i class="fa fa-fast-backward" aria-hidden="true"></i> First</a></li>
            <li class="<?php if ($pageno <= 1) { echo 'disabled'; } ?>">
                <a href="<?php if ($pageno <= 1) { echo '#'; } else { echo "?pageno=" . ($pageno - 1); } ?>"><i class="fa fa-chevron-left" aria-hidden="true"></i> Prev</a>
            </li>
            <li class="<?php if ($pageno >= $total_pages) { echo 'disabled'; } ?>">
                <a href="<?php if ($pageno >= $total_pages) { echo '#'; } else { echo "?pageno=" . ($pageno + 1); } ?>">Next <i class="fa fa-chevron-right" aria-hidden="true"></i></a>
            </li>
            <li><a href="?pageno=<?php echo $total_pages; ?>">Last <i class="fa fa-fast-forward" aria-hidden="true"></i></a></li>
        </ul>
    </div>
</div>

<?php include('footer.php'); ?>
