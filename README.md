# Jenkins trigger builds remotely with parameters using PHP

This PHP script allows to trigger Jenkins jobs or pipelines remotely with customizable parameters, directly from a web page, without logging into Jenkins. It supports dynamic parameters (passed through GET requests) and static parameters, providing real-time output during the build process.

### Requirements
- **Jenkins Instance**: API access enabled.
- **Jenkins Job/Pipeline Token**: A token associated with the job or pipeline for secure authentication.

### Features
- **Trigger Jenkins Builds Remotely**: Initiate Jenkins jobs/pipelines from an external web page.
- **Dynamic and Static Parameters**: Pass parameters dynamically via GET requests or define static values directly in the script.
- **Compilation Output**: Real-time status updates during the build, including messages for queued, running, and completed stages.

### Code Example
```php
$auth_token = "usernamejenkins:tokenjenkins"; // Jenkins user and token
$jenkins_uri = "10.10.0.11:8443"; // Jenkins IP and port
$job_name = "Job-or-pipeline"; // Jenkins job or pipeline name

$parameter1 = $_GET["parameter1"]; // Dynamic GET parameter
$parameter2 = $_GET["parameter2"]; // Dynamic GET parameter
$parameter3 = $_GET["parameter3"]; // Dynamic GET parameter
$parameter4 = $_GET["parameter4"]; // Dynamic GET parameter
$parameter5 = "static_value"; // Static parameter
$ipclient = $_SERVER['REMOTE_ADDR']; // Client IP
```
### Usage
1. Setup Jenkins Token: Generate a Jenkins token linked to the job or pipeline.
2. Configure Parameters: Replace dynamic parameters (e.g., parameter1, parameter2) with values as needed. Set any static parameters in the script directly.
3. Deploy the Script: Host the PHP script on your server.
4. Trigger Builds: Access the PHP script via URL, appending the required GET parameters.
Example URL: http://yourserver.com/trigger.php?parameter1=value1&parameter2=value2&parameter3=value3&parameter4=value4
6. View Output: The script provides live build status updates directly in the web interface.
Includes messages for queue status, job initiation, and completion results.
8. Monitor Build Status: The script polls Jenkins at intervals to update build status, displaying results for queued, running, and completed stages in real-time.

### Developed by
Developed by **Mauro De Gaetanis**.  
Feel free to contribute or contact me for suggestions or improvements.
