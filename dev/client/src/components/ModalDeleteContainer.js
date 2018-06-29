import React from 'react'

export default function ModalDelete(props) {
  if (props.action !== 'delete') return null
  return <div className="modal">
    <div className="modal-inner">
      <div className="modal-inner-header">
        <h3>Delete good</h3>
        <button onClick={(e)=>props.close()}>x</button>
      </div>
      <div className="modal-inner-body">
        <div className="modal-inner-body-text">You want delete good "{props.good.title}". Are you sure?</div>
        <div className="modal-inner-body-btns">
          <button onClick={(e)=>props.delete(props.good.id)}>Yes</button>
          <button onClick={(e)=>props.close()}>No</button>
        </div>  
      </div>
    </div>
  </div>
} 