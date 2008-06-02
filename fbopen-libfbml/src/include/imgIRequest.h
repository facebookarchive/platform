/*
 * DO NOT EDIT.  THIS FILE IS GENERATED FROM imgIRequest.idl
 */

#ifndef __gen_imgIRequest_h__
#define __gen_imgIRequest_h__


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
class imgIContainer; /* forward declaration */

class imgIDecoderObserver; /* forward declaration */

class nsIURI; /* forward declaration */


/* starting interface:    imgIRequest */
#define IMGIREQUEST_IID_STR "ccf705f6-1dd1-11b2-82ef-e18eccf7f7ec"

#define IMGIREQUEST_IID \
  {0xccf705f6, 0x1dd1, 0x11b2, \
    { 0x82, 0xef, 0xe1, 0x8e, 0xcc, 0xf7, 0xf7, 0xec }}

/**
 * imgIRequest interface
 *
 * @author Stuart Parmenter <pavlov@netscape.com>
 * @version 0.1
 * @see imagelib2
 */
class NS_NO_VTABLE imgIRequest : public nsIRequest {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(IMGIREQUEST_IID)

  /**
   * the image container...
   * @return the image object associated with the request.
   * @attention NEED DOCS
   */
  /* readonly attribute imgIContainer image; */
  NS_IMETHOD GetImage(imgIContainer * *aImage) = 0;

  /**
   * Bits set in the return value from imageStatus
   * @name statusflags
   */
  enum { STATUS_NONE = 0 };

  enum { STATUS_SIZE_AVAILABLE = 1 };

  enum { STATUS_LOAD_PARTIAL = 2 };

  enum { STATUS_LOAD_COMPLETE = 4 };

  enum { STATUS_ERROR = 8 };

  enum { STATUS_FRAME_COMPLETE = 16 };

  /**
   * something
   * @attention NEED DOCS
   */
  /* readonly attribute unsigned long imageStatus; */
  NS_IMETHOD GetImageStatus(PRUint32 *aImageStatus) = 0;

  /* readonly attribute nsIURI URI; */
  NS_IMETHOD GetURI(nsIURI * *aURI) = 0;

  /* readonly attribute imgIDecoderObserver decoderObserver; */
  NS_IMETHOD GetDecoderObserver(imgIDecoderObserver * *aDecoderObserver) = 0;

  /* readonly attribute string mimeType; */
  NS_IMETHOD GetMimeType(char * *aMimeType) = 0;

  /**
   * Clone this request; the returned request will have aObserver as the
   * observer.  aObserver will be notified synchronously (before the clone()
   * call returns) with all the notifications that have already been dispatched
   * for this image load.
   */
  /* imgIRequest clone (in imgIDecoderObserver aObserver); */
  NS_IMETHOD Clone(imgIDecoderObserver *aObserver, imgIRequest **_retval) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_IMGIREQUEST \
  NS_IMETHOD GetImage(imgIContainer * *aImage); \
  NS_IMETHOD GetImageStatus(PRUint32 *aImageStatus); \
  NS_IMETHOD GetURI(nsIURI * *aURI); \
  NS_IMETHOD GetDecoderObserver(imgIDecoderObserver * *aDecoderObserver); \
  NS_IMETHOD GetMimeType(char * *aMimeType); \
  NS_IMETHOD Clone(imgIDecoderObserver *aObserver, imgIRequest **_retval); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_IMGIREQUEST(_to) \
  NS_IMETHOD GetImage(imgIContainer * *aImage) { return _to GetImage(aImage); } \
  NS_IMETHOD GetImageStatus(PRUint32 *aImageStatus) { return _to GetImageStatus(aImageStatus); } \
  NS_IMETHOD GetURI(nsIURI * *aURI) { return _to GetURI(aURI); } \
  NS_IMETHOD GetDecoderObserver(imgIDecoderObserver * *aDecoderObserver) { return _to GetDecoderObserver(aDecoderObserver); } \
  NS_IMETHOD GetMimeType(char * *aMimeType) { return _to GetMimeType(aMimeType); } \
  NS_IMETHOD Clone(imgIDecoderObserver *aObserver, imgIRequest **_retval) { return _to Clone(aObserver, _retval); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_IMGIREQUEST(_to) \
  NS_IMETHOD GetImage(imgIContainer * *aImage) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetImage(aImage); } \
  NS_IMETHOD GetImageStatus(PRUint32 *aImageStatus) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetImageStatus(aImageStatus); } \
  NS_IMETHOD GetURI(nsIURI * *aURI) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetURI(aURI); } \
  NS_IMETHOD GetDecoderObserver(imgIDecoderObserver * *aDecoderObserver) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetDecoderObserver(aDecoderObserver); } \
  NS_IMETHOD GetMimeType(char * *aMimeType) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetMimeType(aMimeType); } \
  NS_IMETHOD Clone(imgIDecoderObserver *aObserver, imgIRequest **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->Clone(aObserver, _retval); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class _MYCLASS_ : public imgIRequest
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_IMGIREQUEST

  _MYCLASS_();

private:
  ~_MYCLASS_();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(_MYCLASS_, imgIRequest)

_MYCLASS_::_MYCLASS_()
{
  /* member initializers and constructor code */
}

_MYCLASS_::~_MYCLASS_()
{
  /* destructor code */
}

/* readonly attribute imgIContainer image; */
NS_IMETHODIMP _MYCLASS_::GetImage(imgIContainer * *aImage)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute unsigned long imageStatus; */
NS_IMETHODIMP _MYCLASS_::GetImageStatus(PRUint32 *aImageStatus)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute nsIURI URI; */
NS_IMETHODIMP _MYCLASS_::GetURI(nsIURI * *aURI)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute imgIDecoderObserver decoderObserver; */
NS_IMETHODIMP _MYCLASS_::GetDecoderObserver(imgIDecoderObserver * *aDecoderObserver)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute string mimeType; */
NS_IMETHODIMP _MYCLASS_::GetMimeType(char * *aMimeType)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* imgIRequest clone (in imgIDecoderObserver aObserver); */
NS_IMETHODIMP _MYCLASS_::Clone(imgIDecoderObserver *aObserver, imgIRequest **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


#endif /* __gen_imgIRequest_h__ */
