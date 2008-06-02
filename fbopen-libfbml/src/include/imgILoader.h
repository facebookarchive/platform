/*
 * DO NOT EDIT.  THIS FILE IS GENERATED FROM imgILoader.idl
 */

#ifndef __gen_imgILoader_h__
#define __gen_imgILoader_h__


#ifndef __gen_nsISupports_h__
#include "nsISupports.h"
#endif

#ifndef __gen_nsIRequest_h__
#include "nsIRequest.h"
#endif

/* For IDL files that don't want to include root IDL files. */
#ifndef NS_NO_VTABLE
#define NS_NO_VTABLE
#endif
class imgIDecoderObserver; /* forward declaration */

class imgIRequest; /* forward declaration */

class nsIChannel; /* forward declaration */

class nsILoadGroup; /* forward declaration */

class nsIStreamListener; /* forward declaration */

class nsIURI; /* forward declaration */

class nsISimpleEnumerator; /* forward declaration */


/* starting interface:    imgILoader */
#define IMGILOADER_IID_STR "a32826ff-9e56-4425-a811-97a8dba64ff5"

#define IMGILOADER_IID \
  {0xa32826ff, 0x9e56, 0x4425, \
    { 0xa8, 0x11, 0x97, 0xa8, 0xdb, 0xa6, 0x4f, 0xf5 }}

/**
 * imgILoader interface
 *
 * @author Stuart Parmenter <pavlov@netscape.com>
 * @version 0.3
 * @see imagelib2
 */
class NS_NO_VTABLE imgILoader : public nsISupports {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(IMGILOADER_IID)

  /**
   * Start the load and decode of an image.
   * @param aURI the URI to load
   * @param aInitialDocumentURI the URI that 'initiated' the load -- used for 3rd party cookie blocking
   * @param aReferrerURI the 'referring' URI
   * @param aLoadGroup Loadgroup to put the image load into
   * @param aObserver the observer
   * @param aCX some random data
   * @param aLoadFlags Load flags for the request
   * @param aCacheKey cache key to use for a load if the original
   *                  image came from a request that had post data
   * @param aRequest A newly created, unused imgIRequest object or NULL for one to
                     be created for you.


   * libpr0n does NOT keep a strong ref to the observer; this prevents
   * reference cycles.  This means that callers of loadImage should
   * make sure to Cancel() the resulting request before the observer
   * goes away.
   */
  /* imgIRequest loadImage (in nsIURI aURI, in nsIURI aInitialDocumentURL, in nsIURI aReferrerURI, in nsILoadGroup aLoadGroup, in imgIDecoderObserver aObserver, in nsISupports aCX, in nsLoadFlags aLoadFlags, in nsISupports cacheKey, in imgIRequest aRequest); */
  NS_IMETHOD LoadImage(nsIURI *aURI, nsIURI *aInitialDocumentURL, nsIURI *aReferrerURI, nsILoadGroup *aLoadGroup, imgIDecoderObserver *aObserver, nsISupports *aCX, nsLoadFlags aLoadFlags, nsISupports *cacheKey, imgIRequest *aRequest, imgIRequest **_retval) = 0;

  /**
   * Start the load and decode of an image.
   * @param uri the URI to load
   * @param aObserver the observer
   * @param cx some random data
   *
   * libpr0n does NOT keep a strong ref to the observer; this prevents
   * reference cycles.  This means that callers of loadImageWithChannel should
   * make sure to Cancel() the resulting request before the observer goes away.
   */
  /* imgIRequest loadImageWithChannel (in nsIChannel aChannel, in imgIDecoderObserver aObserver, in nsISupports cx, out nsIStreamListener aListener); */
  NS_IMETHOD LoadImageWithChannel(nsIChannel *aChannel, imgIDecoderObserver *aObserver, nsISupports *cx, nsIStreamListener **aListener, imgIRequest **_retval) = 0;

  /**
   * Checks if a decoder for the an image with the given mime type is available
   * @param mimeType The type to find a decoder for
   * @return true if a decoder is available, false otherwise
   */
  /* boolean supportImageWithMimeType (in string mimeType); */
  NS_IMETHOD SupportImageWithMimeType(const char *mimeType, PRBool *_retval) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_IMGILOADER \
  NS_IMETHOD LoadImage(nsIURI *aURI, nsIURI *aInitialDocumentURL, nsIURI *aReferrerURI, nsILoadGroup *aLoadGroup, imgIDecoderObserver *aObserver, nsISupports *aCX, nsLoadFlags aLoadFlags, nsISupports *cacheKey, imgIRequest *aRequest, imgIRequest **_retval); \
  NS_IMETHOD LoadImageWithChannel(nsIChannel *aChannel, imgIDecoderObserver *aObserver, nsISupports *cx, nsIStreamListener **aListener, imgIRequest **_retval); \
  NS_IMETHOD SupportImageWithMimeType(const char *mimeType, PRBool *_retval); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_IMGILOADER(_to) \
  NS_IMETHOD LoadImage(nsIURI *aURI, nsIURI *aInitialDocumentURL, nsIURI *aReferrerURI, nsILoadGroup *aLoadGroup, imgIDecoderObserver *aObserver, nsISupports *aCX, nsLoadFlags aLoadFlags, nsISupports *cacheKey, imgIRequest *aRequest, imgIRequest **_retval) { return _to LoadImage(aURI, aInitialDocumentURL, aReferrerURI, aLoadGroup, aObserver, aCX, aLoadFlags, cacheKey, aRequest, _retval); } \
  NS_IMETHOD LoadImageWithChannel(nsIChannel *aChannel, imgIDecoderObserver *aObserver, nsISupports *cx, nsIStreamListener **aListener, imgIRequest **_retval) { return _to LoadImageWithChannel(aChannel, aObserver, cx, aListener, _retval); } \
  NS_IMETHOD SupportImageWithMimeType(const char *mimeType, PRBool *_retval) { return _to SupportImageWithMimeType(mimeType, _retval); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_IMGILOADER(_to) \
  NS_IMETHOD LoadImage(nsIURI *aURI, nsIURI *aInitialDocumentURL, nsIURI *aReferrerURI, nsILoadGroup *aLoadGroup, imgIDecoderObserver *aObserver, nsISupports *aCX, nsLoadFlags aLoadFlags, nsISupports *cacheKey, imgIRequest *aRequest, imgIRequest **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->LoadImage(aURI, aInitialDocumentURL, aReferrerURI, aLoadGroup, aObserver, aCX, aLoadFlags, cacheKey, aRequest, _retval); } \
  NS_IMETHOD LoadImageWithChannel(nsIChannel *aChannel, imgIDecoderObserver *aObserver, nsISupports *cx, nsIStreamListener **aListener, imgIRequest **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->LoadImageWithChannel(aChannel, aObserver, cx, aListener, _retval); } \
  NS_IMETHOD SupportImageWithMimeType(const char *mimeType, PRBool *_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->SupportImageWithMimeType(mimeType, _retval); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class _MYCLASS_ : public imgILoader
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_IMGILOADER

  _MYCLASS_();

private:
  ~_MYCLASS_();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(_MYCLASS_, imgILoader)

_MYCLASS_::_MYCLASS_()
{
  /* member initializers and constructor code */
}

_MYCLASS_::~_MYCLASS_()
{
  /* destructor code */
}

/* imgIRequest loadImage (in nsIURI aURI, in nsIURI aInitialDocumentURL, in nsIURI aReferrerURI, in nsILoadGroup aLoadGroup, in imgIDecoderObserver aObserver, in nsISupports aCX, in nsLoadFlags aLoadFlags, in nsISupports cacheKey, in imgIRequest aRequest); */
NS_IMETHODIMP _MYCLASS_::LoadImage(nsIURI *aURI, nsIURI *aInitialDocumentURL, nsIURI *aReferrerURI, nsILoadGroup *aLoadGroup, imgIDecoderObserver *aObserver, nsISupports *aCX, nsLoadFlags aLoadFlags, nsISupports *cacheKey, imgIRequest *aRequest, imgIRequest **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* imgIRequest loadImageWithChannel (in nsIChannel aChannel, in imgIDecoderObserver aObserver, in nsISupports cx, out nsIStreamListener aListener); */
NS_IMETHODIMP _MYCLASS_::LoadImageWithChannel(nsIChannel *aChannel, imgIDecoderObserver *aObserver, nsISupports *cx, nsIStreamListener **aListener, imgIRequest **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* boolean supportImageWithMimeType (in string mimeType); */
NS_IMETHODIMP _MYCLASS_::SupportImageWithMimeType(const char *mimeType, PRBool *_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


#endif /* __gen_imgILoader_h__ */
