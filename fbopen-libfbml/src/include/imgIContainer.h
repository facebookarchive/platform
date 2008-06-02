/*
 * DO NOT EDIT.  THIS FILE IS GENERATED FROM imgIContainer.idl
 */

#ifndef __gen_imgIContainer_h__
#define __gen_imgIContainer_h__


#ifndef __gen_nsISupports_h__
#include "nsISupports.h"
#endif

#ifndef __gen_gfxtypes_h__
#include "gfxtypes.h"
#endif

#ifndef __gen_gfxIFormats_h__
#include "gfxIFormats.h"
#endif

/* For IDL files that don't want to include root IDL files. */
#ifndef NS_NO_VTABLE
#define NS_NO_VTABLE
#endif
class gfxIImageFrame; /* forward declaration */

class imgIContainerObserver; /* forward declaration */


/* starting interface:    imgIContainer */
#define IMGICONTAINER_IID_STR "1a6290e6-8285-4e10-963d-d001f8d327b8"

#define IMGICONTAINER_IID \
  {0x1a6290e6, 0x8285, 0x4e10, \
    { 0x96, 0x3d, 0xd0, 0x01, 0xf8, 0xd3, 0x27, 0xb8 }}

/**
 * gfxIImageContainer interface
 *
 * @author Stuart Parmenter <pavlov@netscape.com>
 * @version 0.1
 * @see "gfx2"
 */
class NS_NO_VTABLE imgIContainer : public nsISupports {
 public: 

  NS_DEFINE_STATIC_IID_ACCESSOR(IMGICONTAINER_IID)

  /**
   * Create a new \a aWidth x \a aHeight sized image container.
   *
   * @param aWidth The width of the container in which all the
   *               gfxIImageFrame children will fit.
   * @param aHeight The height of the container in which all the
   *                gfxIImageFrame children will fit.
   * @param aObserver Observer to send animation notifications to.
   */
  /* void init (in PRInt32 aWidth, in PRInt32 aHeight, in imgIContainerObserver aObserver); */
  NS_IMETHOD Init(PRInt32 aWidth, PRInt32 aHeight, imgIContainerObserver *aObserver) = 0;

  /* readonly attribute gfx_format preferredAlphaChannelFormat; */
  NS_IMETHOD GetPreferredAlphaChannelFormat(gfx_format *aPreferredAlphaChannelFormat) = 0;

  /**
   * The width of the container rectangle.
   */
  /* readonly attribute PRInt32 width; */
  NS_IMETHOD GetWidth(PRInt32 *aWidth) = 0;

  /**
   * The height of the container rectangle.
   */
  /* readonly attribute PRInt32 height; */
  NS_IMETHOD GetHeight(PRInt32 *aHeight) = 0;

  /**
   * Get the current frame that would be drawn if the image was to be drawn now
   */
  /* readonly attribute gfxIImageFrame currentFrame; */
  NS_IMETHOD GetCurrentFrame(gfxIImageFrame * *aCurrentFrame) = 0;

  /* readonly attribute unsigned long numFrames; */
  NS_IMETHOD GetNumFrames(PRUint32 *aNumFrames) = 0;

  /**
   * Animation mode Constants
   *   0 = normal
   *   1 = don't animate
   *   2 = loop once
   */
  enum { kNormalAnimMode = 0 };

  enum { kDontAnimMode = 1 };

  enum { kLoopOnceAnimMode = 2 };

  /* attribute unsigned short animationMode; */
  NS_IMETHOD GetAnimationMode(PRUint16 *aAnimationMode) = 0;
  NS_IMETHOD SetAnimationMode(PRUint16 aAnimationMode) = 0;

  /* gfxIImageFrame getFrameAt (in unsigned long index); */
  NS_IMETHOD GetFrameAt(PRUint32 index, gfxIImageFrame **_retval) = 0;

  /**
   * Adds \a item to the end of the list of frames.
   * @param item frame to add.
   */
  /* void appendFrame (in gfxIImageFrame item); */
  NS_IMETHOD AppendFrame(gfxIImageFrame *item) = 0;

  /* void removeFrame (in gfxIImageFrame item); */
  NS_IMETHOD RemoveFrame(gfxIImageFrame *item) = 0;

  /* void endFrameDecode (in unsigned long framenumber, in unsigned long timeout); */
  NS_IMETHOD EndFrameDecode(PRUint32 framenumber, PRUint32 timeout) = 0;

  /* void decodingComplete (); */
  NS_IMETHOD DecodingComplete(void) = 0;

  /* void clear (); */
  NS_IMETHOD Clear(void) = 0;

  /* void startAnimation (); */
  NS_IMETHOD StartAnimation(void) = 0;

  /* void stopAnimation (); */
  NS_IMETHOD StopAnimation(void) = 0;

  /* void resetAnimation (); */
  NS_IMETHOD ResetAnimation(void) = 0;

  /**
   * number of times to loop the image.
   * @note -1 means forever.
   */
  /* attribute long loopCount; */
  NS_IMETHOD GetLoopCount(PRInt32 *aLoopCount) = 0;
  NS_IMETHOD SetLoopCount(PRInt32 aLoopCount) = 0;

};

/* Use this macro when declaring classes that implement this interface. */
#define NS_DECL_IMGICONTAINER \
  NS_IMETHOD Init(PRInt32 aWidth, PRInt32 aHeight, imgIContainerObserver *aObserver); \
  NS_IMETHOD GetPreferredAlphaChannelFormat(gfx_format *aPreferredAlphaChannelFormat); \
  NS_IMETHOD GetWidth(PRInt32 *aWidth); \
  NS_IMETHOD GetHeight(PRInt32 *aHeight); \
  NS_IMETHOD GetCurrentFrame(gfxIImageFrame * *aCurrentFrame); \
  NS_IMETHOD GetNumFrames(PRUint32 *aNumFrames); \
  NS_IMETHOD GetAnimationMode(PRUint16 *aAnimationMode); \
  NS_IMETHOD SetAnimationMode(PRUint16 aAnimationMode); \
  NS_IMETHOD GetFrameAt(PRUint32 index, gfxIImageFrame **_retval); \
  NS_IMETHOD AppendFrame(gfxIImageFrame *item); \
  NS_IMETHOD RemoveFrame(gfxIImageFrame *item); \
  NS_IMETHOD EndFrameDecode(PRUint32 framenumber, PRUint32 timeout); \
  NS_IMETHOD DecodingComplete(void); \
  NS_IMETHOD Clear(void); \
  NS_IMETHOD StartAnimation(void); \
  NS_IMETHOD StopAnimation(void); \
  NS_IMETHOD ResetAnimation(void); \
  NS_IMETHOD GetLoopCount(PRInt32 *aLoopCount); \
  NS_IMETHOD SetLoopCount(PRInt32 aLoopCount); 

/* Use this macro to declare functions that forward the behavior of this interface to another object. */
#define NS_FORWARD_IMGICONTAINER(_to) \
  NS_IMETHOD Init(PRInt32 aWidth, PRInt32 aHeight, imgIContainerObserver *aObserver) { return _to Init(aWidth, aHeight, aObserver); } \
  NS_IMETHOD GetPreferredAlphaChannelFormat(gfx_format *aPreferredAlphaChannelFormat) { return _to GetPreferredAlphaChannelFormat(aPreferredAlphaChannelFormat); } \
  NS_IMETHOD GetWidth(PRInt32 *aWidth) { return _to GetWidth(aWidth); } \
  NS_IMETHOD GetHeight(PRInt32 *aHeight) { return _to GetHeight(aHeight); } \
  NS_IMETHOD GetCurrentFrame(gfxIImageFrame * *aCurrentFrame) { return _to GetCurrentFrame(aCurrentFrame); } \
  NS_IMETHOD GetNumFrames(PRUint32 *aNumFrames) { return _to GetNumFrames(aNumFrames); } \
  NS_IMETHOD GetAnimationMode(PRUint16 *aAnimationMode) { return _to GetAnimationMode(aAnimationMode); } \
  NS_IMETHOD SetAnimationMode(PRUint16 aAnimationMode) { return _to SetAnimationMode(aAnimationMode); } \
  NS_IMETHOD GetFrameAt(PRUint32 index, gfxIImageFrame **_retval) { return _to GetFrameAt(index, _retval); } \
  NS_IMETHOD AppendFrame(gfxIImageFrame *item) { return _to AppendFrame(item); } \
  NS_IMETHOD RemoveFrame(gfxIImageFrame *item) { return _to RemoveFrame(item); } \
  NS_IMETHOD EndFrameDecode(PRUint32 framenumber, PRUint32 timeout) { return _to EndFrameDecode(framenumber, timeout); } \
  NS_IMETHOD DecodingComplete(void) { return _to DecodingComplete(); } \
  NS_IMETHOD Clear(void) { return _to Clear(); } \
  NS_IMETHOD StartAnimation(void) { return _to StartAnimation(); } \
  NS_IMETHOD StopAnimation(void) { return _to StopAnimation(); } \
  NS_IMETHOD ResetAnimation(void) { return _to ResetAnimation(); } \
  NS_IMETHOD GetLoopCount(PRInt32 *aLoopCount) { return _to GetLoopCount(aLoopCount); } \
  NS_IMETHOD SetLoopCount(PRInt32 aLoopCount) { return _to SetLoopCount(aLoopCount); } 

/* Use this macro to declare functions that forward the behavior of this interface to another object in a safe way. */
#define NS_FORWARD_SAFE_IMGICONTAINER(_to) \
  NS_IMETHOD Init(PRInt32 aWidth, PRInt32 aHeight, imgIContainerObserver *aObserver) { return !_to ? NS_ERROR_NULL_POINTER : _to->Init(aWidth, aHeight, aObserver); } \
  NS_IMETHOD GetPreferredAlphaChannelFormat(gfx_format *aPreferredAlphaChannelFormat) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetPreferredAlphaChannelFormat(aPreferredAlphaChannelFormat); } \
  NS_IMETHOD GetWidth(PRInt32 *aWidth) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetWidth(aWidth); } \
  NS_IMETHOD GetHeight(PRInt32 *aHeight) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetHeight(aHeight); } \
  NS_IMETHOD GetCurrentFrame(gfxIImageFrame * *aCurrentFrame) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetCurrentFrame(aCurrentFrame); } \
  NS_IMETHOD GetNumFrames(PRUint32 *aNumFrames) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetNumFrames(aNumFrames); } \
  NS_IMETHOD GetAnimationMode(PRUint16 *aAnimationMode) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetAnimationMode(aAnimationMode); } \
  NS_IMETHOD SetAnimationMode(PRUint16 aAnimationMode) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetAnimationMode(aAnimationMode); } \
  NS_IMETHOD GetFrameAt(PRUint32 index, gfxIImageFrame **_retval) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetFrameAt(index, _retval); } \
  NS_IMETHOD AppendFrame(gfxIImageFrame *item) { return !_to ? NS_ERROR_NULL_POINTER : _to->AppendFrame(item); } \
  NS_IMETHOD RemoveFrame(gfxIImageFrame *item) { return !_to ? NS_ERROR_NULL_POINTER : _to->RemoveFrame(item); } \
  NS_IMETHOD EndFrameDecode(PRUint32 framenumber, PRUint32 timeout) { return !_to ? NS_ERROR_NULL_POINTER : _to->EndFrameDecode(framenumber, timeout); } \
  NS_IMETHOD DecodingComplete(void) { return !_to ? NS_ERROR_NULL_POINTER : _to->DecodingComplete(); } \
  NS_IMETHOD Clear(void) { return !_to ? NS_ERROR_NULL_POINTER : _to->Clear(); } \
  NS_IMETHOD StartAnimation(void) { return !_to ? NS_ERROR_NULL_POINTER : _to->StartAnimation(); } \
  NS_IMETHOD StopAnimation(void) { return !_to ? NS_ERROR_NULL_POINTER : _to->StopAnimation(); } \
  NS_IMETHOD ResetAnimation(void) { return !_to ? NS_ERROR_NULL_POINTER : _to->ResetAnimation(); } \
  NS_IMETHOD GetLoopCount(PRInt32 *aLoopCount) { return !_to ? NS_ERROR_NULL_POINTER : _to->GetLoopCount(aLoopCount); } \
  NS_IMETHOD SetLoopCount(PRInt32 aLoopCount) { return !_to ? NS_ERROR_NULL_POINTER : _to->SetLoopCount(aLoopCount); } 

#if 0
/* Use the code below as a template for the implementation class for this interface. */

/* Header file */
class _MYCLASS_ : public imgIContainer
{
public:
  NS_DECL_ISUPPORTS
  NS_DECL_IMGICONTAINER

  _MYCLASS_();

private:
  ~_MYCLASS_();

protected:
  /* additional members */
};

/* Implementation file */
NS_IMPL_ISUPPORTS1(_MYCLASS_, imgIContainer)

_MYCLASS_::_MYCLASS_()
{
  /* member initializers and constructor code */
}

_MYCLASS_::~_MYCLASS_()
{
  /* destructor code */
}

/* void init (in PRInt32 aWidth, in PRInt32 aHeight, in imgIContainerObserver aObserver); */
NS_IMETHODIMP _MYCLASS_::Init(PRInt32 aWidth, PRInt32 aHeight, imgIContainerObserver *aObserver)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute gfx_format preferredAlphaChannelFormat; */
NS_IMETHODIMP _MYCLASS_::GetPreferredAlphaChannelFormat(gfx_format *aPreferredAlphaChannelFormat)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute PRInt32 width; */
NS_IMETHODIMP _MYCLASS_::GetWidth(PRInt32 *aWidth)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute PRInt32 height; */
NS_IMETHODIMP _MYCLASS_::GetHeight(PRInt32 *aHeight)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute gfxIImageFrame currentFrame; */
NS_IMETHODIMP _MYCLASS_::GetCurrentFrame(gfxIImageFrame * *aCurrentFrame)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* readonly attribute unsigned long numFrames; */
NS_IMETHODIMP _MYCLASS_::GetNumFrames(PRUint32 *aNumFrames)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute unsigned short animationMode; */
NS_IMETHODIMP _MYCLASS_::GetAnimationMode(PRUint16 *aAnimationMode)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP _MYCLASS_::SetAnimationMode(PRUint16 aAnimationMode)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* gfxIImageFrame getFrameAt (in unsigned long index); */
NS_IMETHODIMP _MYCLASS_::GetFrameAt(PRUint32 index, gfxIImageFrame **_retval)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void appendFrame (in gfxIImageFrame item); */
NS_IMETHODIMP _MYCLASS_::AppendFrame(gfxIImageFrame *item)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void removeFrame (in gfxIImageFrame item); */
NS_IMETHODIMP _MYCLASS_::RemoveFrame(gfxIImageFrame *item)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void endFrameDecode (in unsigned long framenumber, in unsigned long timeout); */
NS_IMETHODIMP _MYCLASS_::EndFrameDecode(PRUint32 framenumber, PRUint32 timeout)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void decodingComplete (); */
NS_IMETHODIMP _MYCLASS_::DecodingComplete()
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void clear (); */
NS_IMETHODIMP _MYCLASS_::Clear()
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void startAnimation (); */
NS_IMETHODIMP _MYCLASS_::StartAnimation()
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void stopAnimation (); */
NS_IMETHODIMP _MYCLASS_::StopAnimation()
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* void resetAnimation (); */
NS_IMETHODIMP _MYCLASS_::ResetAnimation()
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* attribute long loopCount; */
NS_IMETHODIMP _MYCLASS_::GetLoopCount(PRInt32 *aLoopCount)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}
NS_IMETHODIMP _MYCLASS_::SetLoopCount(PRInt32 aLoopCount)
{
    return NS_ERROR_NOT_IMPLEMENTED;
}

/* End of implementation class template. */
#endif


#endif /* __gen_imgIContainer_h__ */
