/***************************************************************************/
/*        Boilerplate: Establish the info.anthonyerutledge.objects.XHR1 namespace.     */
/***************************************************************************/

if (!this.isUndefined(info.anthonyerutledge.objects.XHR1)) {
    throw new Error("The info.anthonyerutledge.objects.XHR1 class is already defined."); 
}

/***************************************************************************/

/* XHR1 extends Ajax */
info.anthonyerutledge.objects.XHR1 = function(xhr, method, url, prefer, acceptMediaType, requestContentType, responseContentType, formValues){
    info.anthonyerutledge.objects.Ajax.call(this, xhr, method, url, prefer, acceptMediaType, requestContentType, responseContentType);
    this.queryString = null;
    
    if (!this.isNull(formValues)) {
        this.queryString = this.getQueryString(formValues);
    }
};

/* Link prototypes. */
info.anthonyerutledge.objects.XHR1.prototype = Object.create(info.anthonyerutledge.objects.Ajax.prototype, {
    constructor: {
       configurable: true,
       enumerable: true, 
       value: info.anthonyerutledge.objects.XHR1,
       writable: true
   }
});

info.anthonyerutledge.objects.XHR1.prototype.ajaxPost = function() {
    this.xhr.open(this.method, this.url, true);
    this.setCoreHttpHeaders();
    this.xhr.setRequestHeader("Content-Length", this.queryString.length);
    this.xhr.send(this.queryString);
};

info.anthonyerutledge.objects.XHR1.prototype.ajaxGet = function() {
    if (!this.isNull(this.queryString)) {
        this.url = this.url + "?" + this.queryString;
    }

    this.xhr.open(this.method, this.url, true);
    this.setCoreHttpHeaders();
    this.xhr.send(null);
};

/**
 * XMLHttpRequest Level 1 event listener.
 */
info.anthonyerutledge.objects.Ajax.prototype.getXHRHandler = function(responseContentType, webpage, destinationId) {
    return function(e) { // This is done so that I can pass arguments to an event handler.
        if (! this instanceof XMLHttpRequest) {
            throw new TypeError("This XHR1 listener needs to be associated with an XMLHttpRequest instance.");
        }
        
        if ((this.readyState === 4) && (this.status === 200) && (this.getResponseHeader("Content-Type") === responseContentType)) {
            switch (responseContentType) {
                case "application/json; charset=UTF-8":
                    webpage.processJSONResponse(JSON.parse(this.responseText));
                    break;
                case "text/xml; charset=UTF-8":
                    webpage.processXMLResponse(this.responseXML, destinationId);
                    break;
                case "text/html; charset=UTF-8":
                    webpage.processHTMLResponse(this.responseXML, destinationId);
                    break;
                case "text/plain; charset=UTF-8":
                    webpage.processTextResponse(this.responseText, destinationId);
                    break;
                default:
                    throw new Error("Unsupported media type has been returned by the server: " + responseContentType);
            }
        }
    };
};

info.anthonyerutledge.objects.XHR1.prototype.request = function(webpage, destinationId) {
    this.addXHRHandler(this.xhr, "readystatechange", this.getXHRHandler(this.responseContentType, webpage, destinationId));
    this.send();
};