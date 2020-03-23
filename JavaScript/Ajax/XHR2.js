/***************************************************************************/
/*       Boilerplate: Establish the info.anthonyerutledge.objects.XHR2 namespace.      */
/***************************************************************************/

if (!this.isUndefined(info.anthonyerutledge.objects.XHR2)) {
    throw new Error("The info.anthonyerutledge.objects.XHR2 class is already defined."); 
}

/***************************************************************************/

/* XHR2 extends Ajax */
info.anthonyerutledge.objects.XHR2 = function(xhr, method, url, prefer, acceptMediaType, requestContentType, responseContentType, formValues) {
    info.anthonyerutledge.objects.Ajax.call(this, xhr, method, url, prefer, acceptMediaType, requestContentType, responseContentType);
    this.queryString = null;
    
    if (!this.isNull(formValues)) {
        this.queryString = this.getQueryString(formValues);
    }
};

/* Link prototypes. */
info.anthonyerutledge.objects.XHR2.prototype = Object.create(info.anthonyerutledge.objects.Ajax.prototype, {
    constructor: {
        configurable: true,
        enumerable: true, 
        value: info.anthonyerutledge.objects.XHR2,
        writable: true
    }
});

/**
 * Set the XHR responseType property.
 * https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/responseType
 */
info.anthonyerutledge.objects.XHR2.prototype.setXHRResponseType = function() {
    switch (this.acceptMediaType) {
        case "application/json":
            this.xhr.responseType = "json";
            break;
        case "text/xml":
        case "text/html":
            this.xhr.responseType = "document";
            break;
        case "text/plain":
            this.xhr.responseType = "text";
            break;
        default:
            throw new Error("Cannot set xhr.respsoneType to: " + this.acceptMediaType);
    }
};

/**
 * Prepare the state of an XMLHttpRequest object for making an HTTP request.
 */
info.anthonyerutledge.objects.XHR2.prototype.prepXHR = function() {
    this.xhr.open(this.method, this.url, true);
    this.setXHRResponseType();
    this.setCoreHttpHeaders();
    //this.xhr.setRequestHeader('Content-Length', this.queryString.length);
};

/**
 * Open and send an AJAX HTTP POST request.
 */
info.anthonyerutledge.objects.XHR2.prototype.ajaxPost = function() {
    this.xhr.send(this.queryString);
};

/**
 * Open and send an AJAX HTTP GET request.
 */
info.anthonyerutledge.objects.XHR2.prototype.ajaxGet = function() {
    if (this.isNull(this.queryString)) {
        this.xhr.send();
        return;
    }
    
    this.xhr.send(this.queryString);
};

/**
 * XMLHttpRequest Level 2 event listener.
 */
info.anthonyerutledge.objects.Ajax.prototype.getXHRHandler = function(responseContentType, webpage, destinationId) {
    return function(e) { // This is done so that I can pass arguments to an event handler.
        if (! this instanceof XMLHttpRequest) {
            throw new TypeError("This XHR1 listener needs to be associated with an XMLHttpRequest instance.");
        }

        if ((this.status === 200) && (this.getResponseHeader("Content-Type") === responseContentType)) {                
            switch (responseContentType) {
                case "application/json; charset=UTF-8":
                    webpage.processJSONResponse(this.response);
                    break;
                case "text/xml; charset=UTF-8":
                    webpage.processXMLResponse(this.response, destinationId);
                    break;
                case "text/html; charset=UTF-8":
                    webpage.processHTMLResponse(this.response, destinationId);
                    break;
                case "text/plain; charset=UTF-8":
                    webpage.processTextResponse(this.response, destinationId);
                    break;
                default:
                    throw new Error("Unsupported media type has been returned by the server.");
            }
        }
    };
};

info.anthonyerutledge.objects.XHR2.prototype.request = function(webpage, destinationId) {
    this.addXHRHandler(this.xhr, "load", this.getXHRHandler(this.responseContentType, webpage, destinationId));
    this.prepXHR();
    this.send();
};