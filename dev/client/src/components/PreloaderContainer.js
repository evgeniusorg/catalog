import React from 'react'

export default function PreloaderContainer(props) {
    return <div className="preloader">
        <div className="preloader-inner">
          <div className="preloader-inner-bounce bounce-1"></div>
          <div className="preloader-inner-bounce bounce-2"></div>
          <div className="preloader-inner-bounce bounce-3"></div>
        </div>
    </div>
}