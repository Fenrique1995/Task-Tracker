<?php

define('TASKS_FILE', 'tasks.json');

function loadTasks() {
    if (!file_exists(TASKS_FILE)) {
        return [];
    }
    $tasksJson = file_get_contents(TASKS_FILE);
    return json_decode($tasksJson, true) ?: [];
}

function saveTasks($tasks) {
    file_put_contents(TASKS_FILE, json_encode($tasks, JSON_PRETTY_PRINT));
}

function generateTaskId($tasks) {
    if (empty($tasks)) {
        return 1;
    }
    return max(array_column($tasks, 'id')) + 1;
}

function addTask($description) {
    $tasks = loadTasks();
    $taskId = generateTaskId($tasks);
    $task = [
        'id' => $taskId,
        'description' => $description,
        'status' => 'todo',
        'createdAt' => date('c'),
        'updatedAt' => date('c'),
    ];
    $tasks[] = $task;
    saveTasks($tasks);
    echo "Task added successfully (ID: $taskId)\n";
}

function updateTask($taskId, $description) {
    $tasks = loadTasks();
    foreach ($tasks as &$task) {
        if ($task['id'] == $taskId) {
            $task['description'] = $description;
            $task['updatedAt'] = date('c');
            saveTasks($tasks);
            echo "Task updated successfully.\n";
            return;
        }
    }
    echo "Task not found.\n";
}

function deleteTask($taskId) {
    $tasks = loadTasks();
    $tasks = array_filter($tasks, function ($task) use ($taskId) {
        return $task['id'] != $taskId;
    });
    saveTasks(array_values($tasks));
    echo "Task deleted successfully.\n";
}

function markTask($taskId, $status) {
    $tasks = loadTasks();
    foreach ($tasks as &$task) {
        if ($task['id'] == $taskId) {
            $task['status'] = $status;
            $task['updatedAt'] = date('c');
            saveTasks($tasks);
            echo "Task marked as $status.\n";
            return;
        }
    }
    echo "Task not found.\n";
}

function listTasks($filterStatus = null) {
    $tasks = loadTasks();
    if ($filterStatus) {
        $tasks = array_filter($tasks, function ($task) use ($filterStatus) {
            return $task['status'] === $filterStatus;
        });
    }
    if (empty($tasks)) {
        echo "No tasks found.\n";
        return;
    }
    foreach ($tasks as $task) {
        echo "ID: {$task['id']}, Description: {$task['description']}, Status: {$task['status']}, Created At: {$task['createdAt']}, Updated At: {$task['updatedAt']}\n";
    }
}

if ($argc < 2) {
    echo "Usage: php task-cli.php <command> [<args>]\n";
    exit(1);
}

$command = $argv[1];

switch ($command) {
    case 'add':
        if ($argc === 3) {
            addTask($argv[2]);
        } else {
            echo "Usage: php task-cli.php add <description>\n";
        }
        break;

    case 'update':
        if ($argc === 4) {
            updateTask($argv[2], $argv[3]);
        } else {
            echo "Usage: php task-cli.php update <task_id> <description>\n";
        }
        break;

    case 'delete':
        if ($argc === 3) {
            deleteTask($argv[2]);
        } else {
            echo "Usage: php task-cli.php delete <task_id>\n";
        }
        break;

    case 'mark-in-progress':
        if ($argc === 3) {
            markTask($argv[2], 'in-progress');
        } else {
            echo "Usage: php task-cli.php mark-in-progress <task_id>\n";
        }
        break;

    case 'mark-done':
        if ($argc === 3) {
            markTask($argv[2], 'done');
        } else {
            echo "Usage: php task-cli.php mark-done <task_id>\n";
        }
        break;

    case 'list':
        if ($argc === 2) {
            listTasks();
        } elseif ($argc === 3) {
            listTasks($argv[2]);
        } else {
            echo "Usage: php task-cli.php list [status]\n";
        }
        break;

    default:
        echo "Invalid command or arguments.\n";
        break;
}
?>