<?php
/**
 * index.php
 * 
 * UI with procedure buttons
 */

include 'inc/header.php';
require 'db/config.php';

// open db connection
$db = (new DB())->get_db_connection();
?>

<body>

    <!-- main container -->
    <div class="container" id="index_container">

        <!-- page title -->
        <div class="row text-center">
            <div class="col">
                <h1 class="w3-large"><i class="fa fa-cogs"></i> CCC ADMIN <i class="fa fa-cogs"></i></h1>
            </div>
        </div>

        <!-- database buttons -->
        <div class="row" id="database_row">
            <div class="col-md-3">
                <a href="db/initDB.php">
                    <div class="card bg-success" id="initDB">
                        <div class="card-body">
                            <i class="fa fa-database"></i>&nbsp;
                            <span class="card-name">Initialize DB</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="db/dropDB.php">
                    <div class="card bg-danger" id="dropDB">
                        <div class="card-body">
                            <i class="fa fa-trash"></i>&nbsp;
                            <span class="card-name">Drop DB</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <div class="card" id="db_status">
                    <div class="card-body">
                        <i class="fa fa-info"></i>
                        &nbsp;
                        <?php if (isset($db)) { ?>
                            <span style="color:green">DB INITIALIZED</span>
                        <?php } else { ?>
                            <span style="color:red">DB DROPPED</span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- buttons visible when database is on -->
        <?php if (isset($db)) { ?>
            <!-- account buttons -->
            <div class="row" id="account_row">
                <div class="col-md-3">
                    <a href="account.php?register">
                        <div class="card bg-primary">
                            <div class="card-body">
                                <i class="fa fa-address-card"></i>
                                <span class="card-name">Register Account</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="account.php?close">
                        <div class="card bg-primary">
                            <div class="card-body">
                                <i class="fa fa-ban"></i>
                                <span class="card-name">Close Account</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="queries.php">
                        <div class="card bg-primary">
                            <div class="card-body">
                                <i class="fa fa-question-circle"></i>
                                <span class="card-name">Queries</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- transactions buttons -->
            <div class="row" id="transactions_row">
                <div class="col-md-3">
                    <a href="transactions.php?buy">
                        <div class="card bg-secondary">
                            <div class="card-body">
                                <i class="fa fa-credit-card-alt"></i>
                                <span class="card-name">Buy</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="transactions.php?refund">
                        <div class="card bg-secondary">
                            <div class="card-body">
                                <i class="fa fa-money"></i>
                                <span class="card-name">Refund</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="transactions.php?payoff">
                        <div class="card bg-secondary">
                            <div class="card-body">
                                <i class="fa fa-check-circle"></i>
                                <span class="card-name">Payoff</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- statistics buttons -->
            <div class="row" id="statistics_row">
                <div class="col-md-3">
                    <a href="info.php?good_clients">
                        <div class="card bg-info">
                            <div class="card-body">
                                <i class="fa fa-user-plus"></i>
                                <span class="card-name">Good Clients</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="info.php?bad_clients">
                        <div class="card bg-info">
                            <div class="card-body">
                                <i class="fa fa-user-times"></i>
                                <span class="card-name">Bad Clients</span>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-3">
                    <a href="info.php?seller_of_the_month">
                        <div class="card bg-info">
                            <div class="card-body">
                                <i class="fa fa-user-circle"></i>
                                <span class="card-name">Seller Of The Month</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        <?php } ?>

    </div> <!-- end-of-container -->

    <?php
    // close the db connection
    if (isset($db)) $db->close();
    include 'inc/footer.php';
    ?>