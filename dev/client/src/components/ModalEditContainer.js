import React from 'react'

export default class ModalEdit extends React.Component {
  constructor(props){
    super(props);
    this.state = {
      good: {},
      validate: {}
    }

  }

  verify(e){
    e.preventDefault()

    let validate = {
      title: false,
      description: false,
      price: false,
      img: false,
    }

    let status = false
    if (this.state.good.title.trim() === '') {
      validate.title = 'Title is empty!'
      status = true
    }

    if (/^\d+(.\d{2})?$/ig.test(this.state.good.price) === false) {
      validate.price = 'Price is not valid!'
      status = true
    }

    if (this.state.good.description.trim() === '') {
      validate.description = 'Description is empty!'
      status = true
    }

    if (this.state.good.img.trim() === '') {
      validate.img = true
      status = true
    }

    if (status) {
       this.setState({validate})
      return
    }

    if(this.props.action === 'add')
      this.props.add(this.state.good)
    else
      this.props.edit(this.state.good)
  }

  changeFile(e){
    let reader = new FileReader()
    let file = e.target.files[0]

    let good = Object.assign({}, this.state.good)

    reader.onloadend = () => {
      good.img = reader.result
      this.setState({good})
    }

    reader.readAsDataURL(file)
  }

  changeInput(type, event){
    let good = Object.assign({}, this.state.good)
    good[type] = event.target.value
    this.setState({good})
  }

  componentWillReceiveProps(newxProps){
    this.setState({good: newxProps.good, validate: {
      title: false,
      description: false,
      price: false,
      img: false,
    }})
  }

  render() {
    if (this.props.action !== 'add' && this.props.action !== 'edit') return null

    return <div className="modal">
      <div className="modal-inner">
        <div className="modal-inner-header">
          <h3>{this.state.good.id ? `Edit good "${this.state.good.title}"` : 'Added new good'}</h3>
          <button onClick={(e)=>this.props.close()}>x</button>
        </div>
        <div className="modal-inner-body">
          <form name="editor" className="editor-form">
            <div className="editor-form-left">
              <div className={this.state.validate.img ? 'editor-form-left-img error': 'editor-form-left-img'}>
                <img alt={this.state.good.title.substr(0, 15)} src={this.state.good.img} />
              </div>
              <input type="file" onChange={(e)=>this.changeFile(e)} />
            </div>
            <div className="editor-form-right">
              <div className="editor-form-input">
                {this.state.validate.title && <span>{this.state.validate.title}</span>}
                <input type="text" placeholder="Title" value={this.state.good.title} onChange={(e)=>this.changeInput('title', e)}/>
              </div>
              <div className="editor-form-input">
                {this.state.validate.price && <span>{this.state.validate.price}</span>}
                <input type="text" placeholder="Price" value={this.state.good.price} onChange={(e)=>this.changeInput('price', e)} />
              </div>
              <div className="editor-form-input">
                {this.state.validate.description && <span>{this.state.validate.description}</span>}
                <textarea placeholder="Description" onChange={(e)=>this.changeInput('description', e)} value={this.state.good.description}></textarea>
              </div>
              <div className="editor-form-right-btns">
                <button disabled={this.props.count > 0} type="submit" onClick={(e)=>this.verify(e)}>{this.props.count > 0 ? 'Wait...' : 'Save'}</button>
                <button onClick={(e)=>this.props.close()}>Cancel</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  } 
}
