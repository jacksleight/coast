# Middleware

## Return Values and Error Handling

Middleware return values are handeled as follows:

* **null**  
	The request has not been completed, run further middleware.
* **true** (or a value that will be cast to true)  
	The request has been completed successfully, do not run any further middleware.
* **false** (or a value that will be cast to false)  
	The request has been completed unsuccessfully, do not run any further middleware, call the app's not found handler.

If no middleware completes the request the not found handler will be called. Any uncaught exceptions thrown during execution of middleware will be sent to the app's error handler.