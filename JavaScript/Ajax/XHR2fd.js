/******************************************************************************/
/*         Boilerplate: Establish the info.anthonyerutledge.objects.XHR2fd namespace.     */
/******************************************************************************/

if (!this.isUndefined(info.anthonyerutledge.objects.XHR2fd)) {
    throw new error("The info.anthonyerutledge.objects.XHR2fd class is already defined."); 
}

/***************************************************************************/

/* XHR2fd extends XHR2 */
info.anthonyerutledge.objects.XHR2fd = function(xhr, method, url, prefer, acceptMediaType, requestContentType, responseContentType, formValues) {
    info.anthonyerutledge.objects.XH2.call(this, xhr, method, url, prefer, acceptMediaType, requestContentType, responseContentType, null);
    this.formData = this.getFormData(formValues);
};

/* Link prototypes. */
info.anthonyerutledge.objects.XHR2fd.prototype = Object.create(info.anthonyerutledge.objects.XHR2.prototype, {
                                                                            constructor: {
                                                                                configurable: true,
                                                                                enumerable: true, 
                                                                                value: info.anthonyerutledge.objects.XHR2fd,
                                                                                writable: true
                                                                            }
                                                                      });

info.anthonyerutledge.objects.XHR2fd.prototype.getFormData = function(formValues)
{
    var fd = new FormData();
    fd.append("ajaxFlag", "1");
    fd.append("nocache", this.getRandomNumber());
    
    for (var name in formValues) {
        if (this.isNotRegObjProp(formValues, name)) {
            continue;
        }

        fd.append(name, formValues[name].toString());
    }
    
    return fd;
};

info.anthonyerutledge.objects.XHR2fd.prototype.ajaxPost = function()
{
    this.xhr.send(this.formData);
};

info.anthonyerutledge.objects.XHR2fd.prototype.request = function(webpage, destinationId)
{
    var handler = this.xhr2Listener(this.responseContentType, webpage, destinationId);
    this.addXHRHandler(this.xhr, "load", handler);
    this.prepXHR();
    this.ajaxPost();
};
