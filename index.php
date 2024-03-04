<?php
$path_prefix = '';

if ( isset( $_SERVER['PATH_INFO'] ) ) {
	$path_count = substr_count( $_SERVER['PATH_INFO'], '/' ) - 1;

	for ( $i = 0; $i < $path_count; $i++ ) {
		$path_prefix .= '../';
	}

	if ( strpos( $_SERVER['PATH_INFO'], '/api/users' ) !== false ) {
		try {
			$db = new PDO( 'sqlite:database.db' );
		} catch ( PDOException $e ) {
			exit( $e->getMessage() );
		}

		if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
			if ( strpos( $_SERVER['PATH_INFO'], '/exercises' ) !== false ) {
				preg_match( '~\/api\/users\/(.*)\/.*~', $_SERVER['PATH_INFO'], $matches );
				$user_id = $matches[1];

				if ( empty( $user_id ) ) {
					header( 'Content-Type: application/json; charset=utf-8' );
					echo json_encode( [
						'error' => 'user_id is required',
					] );
					exit;
				}

				if ( isset( $_GET['limit'] ) && ! empty( $_GET['limit'] ) && ! is_numeric( $_GET['limit'] ) ) {
					header( 'Content-Type: application/json; charset=utf-8' );
					echo json_encode( [
						'error' => 'limit is not a number',
					] );
					exit;
				}

				$user_id = (int) $user_id;
				$from = false;
				$to = false;
				$limit = (int) $_GET['limit'];

				if ( ! empty( $_GET['from'] ) ) {
					$from = $_GET['from'];

					if ( $from = date_create( $from, timezone_open( 'UTC' ) ) ) {
						$from = date_format( $from, 'Y-m-d' );
					} else {
						header( 'Content-Type: application/json; charset=utf-8' );
						echo json_encode( [
							'error' => 'from date is invalid',
						] );
						exit;
					}
				}

				if ( ! empty( $_GET['to'] ) ) {
					$to = $_GET['to'];

					if ( $to = date_create( $to, timezone_open( 'UTC' ) ) ) {
						$to = date_format( $to, 'Y-m-d' );
					} else {
						header( 'Content-Type: application/json; charset=utf-8' );
						echo json_encode( [
							'error' => 'to date is invalid',
						] );
						exit;
					}
				}

				$user = get_user( $user_id );

				if ( $user ) {
					$exercises = get_exercises( $user_id, $from, $to, $limit );

					header( 'Content-Type: application/json; charset=utf-8' );
					echo json_encode( [
						'user_id' => $user_id,
						'username' => $user['username'],
						'log' => $exercises,
						'count' => count( $exercises ),
					] );
					exit;
				} else {
					header( 'Content-Type: application/json; charset=utf-8' );
					echo json_encode( [
						'error' => 'user not found',
					] );
					exit;
				}
			} else {
				header( 'Content-Type: application/json; charset=utf-8' );
				echo json_encode( get_users() );
				exit;
			}
		} elseif ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			if ( strpos( $_SERVER['PATH_INFO'], '/exercises' ) !== false ) {
				if ( empty( $_POST['user_id'] ) ) {
					header( 'Content-Type: application/json; charset=utf-8' );
					echo json_encode( [
						'error' => 'user_id is required',
					] );
					exit;
				}

				if ( empty( $_POST['description'] ) ) {
					header( 'Content-Type: application/json; charset=utf-8' );
					echo json_encode( [
						'error' => 'description is required',
					] );
					exit;
				}

				if ( empty( $_POST['duration'] ) && $_POST['duration'] !== '0' ) {
					header( 'Content-Type: application/json; charset=utf-8' );
					echo json_encode( [
						'error' => 'duration is required',
					] );
					exit;
				}

				if ( ! is_numeric( $_POST['duration'] ) ) {
					header( 'Content-Type: application/json; charset=utf-8' );
					echo json_encode( [
						'error' => 'duration is not a number',
					] );
					exit;
				}

				$user_id = (int) $_POST['user_id'];
				$description = $_POST['description'];
				$duration = (int) $_POST['duration'];
				$date = isset( $_POST['date'] ) ? $_POST['date'] : 'now';

				if ( $date = date_create( $date, timezone_open( 'UTC' ) ) ) {
					$date = date_format( $date, 'Y-m-d' );
				} else {
					header( 'Content-Type: application/json; charset=utf-8' );
					echo json_encode( [
						'error' => 'date is invalid',
					] );
					exit;
				}

				$user = get_user( $user_id );

				if ( $user ) {
					if ( add_exercise( $user_id, $description, $duration, $date ) ) {
						header( 'Content-Type: application/json; charset=utf-8' );
						echo json_encode( [
							'user_id' => $user_id,
							'username' => $user['username'],
							'description' => $description,
							'duration' => $duration,
							'date' => $date,
						] );
						exit;
					} else {
						header( 'Content-Type: application/json; charset=utf-8' );
						echo json_encode( [
							'error' => 'could not add exercise',
						] );
						exit;
					}
				} else {
					header( 'Content-Type: application/json; charset=utf-8' );
					echo json_encode( [
						'error' => 'user not found',
					] );
					exit;
				}
			} else {
				if ( ! empty( $_POST['username'] ) ) {
					$username = trim( $_POST['username'] );
					$user_id = get_user_id( $username );

					if ( $user_id ) {
						header( 'Content-Type: application/json; charset=utf-8' );
						echo json_encode( [
							'error' => 'username already exists',
						] );
						exit;
					} else {
						if ( add_user( $username ) ) {
							$user_id = get_user_id( $username );

							header( 'Content-Type: application/json; charset=utf-8' );
							echo json_encode( [
								'user_id' => $user_id,
								'username' => $username,
							] );
							exit;
						} else {
							header( 'Content-Type: application/json; charset=utf-8' );
							echo json_encode( [
								'error' => 'could not add user',
							] );
							exit;
						}
					}
				} else {
					header( 'Content-Type: application/json; charset=utf-8' );
					echo json_encode( [
						'error' => 'username is required',
					] );
					exit;
				}
			}
		}
	} else {
		redirect_to_index();
	}
}

function redirect_to_index() {
	global $path_prefix;

	if ( $path_prefix == '' ) {
		$path_prefix = './';
	}

	header( "Location: $path_prefix" );
	exit;
}

function get_users() {
	global $db;

	$query = $db->query( "SELECT * FROM users" );
	$result = $query->fetchAll( PDO::FETCH_ASSOC );

	return $result ? $result : [];
}

function get_user( $user_id ) {
	global $db;

	$query = $db->query( "SELECT * FROM users WHERE id = {$db->quote( $user_id )}" );
	$result = $query->fetchAll( PDO::FETCH_ASSOC );

	return $result ? $result[0] : false;
}

function get_user_id( $username ) {
	global $db;

	$query = $db->query( "SELECT id FROM users WHERE username = {$db->quote( $username )}" );
	$result = $query->fetchAll( PDO::FETCH_ASSOC );

	return $result ? $result[0]['id'] : false;
}

function add_user( $username ) {
	global $db;

	$data = [
		'username' => $username,
	];
	$sth = $db->prepare( 'INSERT INTO users (username) VALUES (:username)' );
	return $sth->execute( $data );
}

function add_exercise( $user_id, $description, $duration, $date ) {
	global $db;

	$data = [
		'user_id' => $user_id,
		'description' => $description,
		'duration' => $duration,
		'date' => $date,
	];
	$sth = $db->prepare( 'INSERT INTO exercises (user_id, description, duration, date) VALUES (:user_id, :description, :duration, :date)' );
	return $sth->execute( $data );
}

function get_exercises( $user_id, $from = false, $to = false, $limit = false ) {
	global $db;

	$query = "SELECT description, duration, date FROM exercises WHERE user_id = {$db->quote( $user_id )}";

	if ( $from ) {
		$query .= " AND date >= {$db->quote( $from )}";
	}

	if ( $to ) {
		$query .= " AND date <= {$db->quote( $to )}";
	}

	$query .= ' ORDER BY date ASC';

	if ( $limit ) {
		$query .= " LIMIT $limit";
	}

	$query = $db->query( $query );
	$result = $query->fetchAll( PDO::FETCH_ASSOC );

	return $result ? $result : [];
}
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Exercise Tracker</title>
	<meta name="description" content="freeCodeCamp - APIs and Microservices Project: Exercise Tracker REST API">
	<link rel="icon" type="image/x-icon" href="<?php echo $path_prefix; ?>favicon.ico">
	<link rel="stylesheet" href="<?php echo $path_prefix; ?>assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo $path_prefix; ?>assets/css/style.min.css">
	<script src="<?php echo $path_prefix; ?>assets/js/script.min.js"></script>
</head>
<body>
	<div class="container">
		<div class="p-4 my-4 bg-light rounded-3">
			<div class="row">
				<div class="col">
					<h1 id="title" class="text-center">Exercise Tracker</h1>

					<div class="text-center">
						<div class="row">
							<div class="col">
								<form action="<?php echo $path_prefix; ?>api/users" method="post">
									<h3>Create a new user</h3>
									<p><code>POST /api/users</code></p>
									<input type="text" name="username" placeholder="username" required>
									<input type="submit" value="Submit">
								</form>

								<form id="add-exercise-form" action="<?php echo $path_prefix; ?>api/users/user_id/exercises" method="post">
									<h3>Add exercise</h3>
									<p><code>POST /api/users/user_id/exercises</code></p>
									<input type="text" name="user_id" placeholder="user_id*" required>
									<input type="text" name="description" placeholder="description*" required>
									<input type="text" name="duration" placeholder="duration (minutes)*" required>
									<input type="text" name="date" placeholder="date (yyyy-mm-dd)">
									<input type="submit" value="Submit">
								</form>

								<form id="get-exercises-form" action="<?php echo $path_prefix; ?>api/users/user_id/exercises" method="get">
									<h3>Get user's exercise log</h3>
									<p><code>GET /api/users/user_id/exercises[?from][&amp;to][&amp;limit]</code></p>
									<input type="text" class="user_id" placeholder="user_id*" required>
									<input type="text" name="from" placeholder="from (yyyy-mm-dd)">
									<input type="text" name="to" placeholder="to (yyyy-mm-dd)">
									<input type="text" name="limit" placeholder="limit">
									<input type="submit" value="Submit">
								</form>
							</div>
						</div>
					</div>

					<div>
						<p><strong>GET all users</strong>: <a href="<?php echo $path_prefix; ?>api/users" target="_blank">/api/users</a></p>
					</div>

					<div class="footer text-center">by <a href="https://www.freecodecamp.org" target="_blank">freeCodeCamp</a> & <a href="https://www.freecodecamp.org/adam777" target="_blank">Adam</a> | <a href="https://github.com/Adam777Z/freecodecamp-project-exercise-tracker-php" target="_blank">GitHub</a></div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>