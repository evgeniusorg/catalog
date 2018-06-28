import React from 'react'
import FA  from 'react-fontawesome'
import CONFIG from '../config'

export class App extends React.Component {
  constructor(props){
    super(props);

    this.state = {
      goods: {
        1: {
          id: 1,
          title: 'Cat 1',
          img: 'https://1079638729.rsc.cdn77.org/pic/v2/gallery/preview/oshki_oty_otiki-zhivotnye-40766.jpg',
          description: 'test description. asd asd asd as da sdas da sd a sd as d asd a d asd ads asdasd aasdasda sda sd a sd asd asd a sd as da sd a sd as da d a sd a sd ad a',
          price: 2000,
        },
        2: {
          id: 2,
          title: 'Cat 2',
          img: 'https://1079638729.rsc.cdn77.org/pic/v2/gallery/preview/oshki_oty_otiki-zhivotnye-40766.jpg',
          description: 'test description',
          price: 3000,
        },
        3: {
          id: 3,
          title: 'Cat 3',
          img: 'https://1079638729.rsc.cdn77.org/pic/v2/gallery/preview/oshki_oty_otiki-zhivotnye-40766.jpg',
          description: 'test description',
          price: 3000,
        },
        4: {
          id: 4,
          title: 'Cat 4',
          img: 'https://1079638729.rsc.cdn77.org/pic/v2/gallery/preview/oshki_oty_otiki-zhivotnye-40766.jpg',
          description: 'test description',
          price: 3000,
        },5: {
          id: 5,
          title: 'Cat 5',
          img: 'https://1079638729.rsc.cdn77.org/pic/v2/gallery/preview/oshki_oty_otiki-zhivotnye-40766.jpg',
          description: 'test description',
          price: 3000,
        }
        ,6: {
          id: 6,
          title: 'Cat 6',
          img: 'https://1079638729.rsc.cdn77.org/pic/v2/gallery/preview/oshki_oty_otiki-zhivotnye-40766.jpg',
          description: 'test description',
          price: 3000,
        }
        ,7: {
          id: 7,
          title: 'Cat 7',
          img: 'https://1079638729.rsc.cdn77.org/pic/v2/gallery/preview/oshki_oty_otiki-zhivotnye-40766.jpg',
          description: 'test description',
          price: 3000,
        }
      },
      sorting: 'id',
      offset: 0,
      limit: 100,
    }
  }

  //default list
  componentDidMount(){
    this.load(this.state.sorting, this.state.limit, this.state.offset);
  }

  //load data from server
  load(sorting, limit, offset){
    let options = {
      method: 'get',
      json: true,
    }
    fetch(`${CONFIG.URL_API}goods?sorting=${sorting}&limit=${limit}&offset=${offset}`, options).then((response)=>{
      let goods = this.state.goods
      Object.values(response).forEach(e=> goods[e.id] = e.id)
      this.setState({goods})
    },(error)=>{
      console.log(error)
    })
  }

  //save new good
  add(){

  }

  //edit good
  editGood(id){ 

  }

  //delete good
  deleteGood(id){
    let options = {
      method: 'delete',
    }
    fetch(`${CONFIG.URL_API}goods/${id}`, options).then((response)=>{
      let goods = this.state.goods
      delete goods[id]
      this.setState({goods})
    },(error)=>{
      console.log(error)
    })
  }

  //change type of sorting
  changeSorting(sorting){
    this.setState({sorting, offset: 0, goods: {}})
    this.load(sorting, this.state.limit, this.state.offset)
  }

  //change limit
  changeLimit(limit){
    this.setState({limit, offset: 0, goods: {}})
    this.load(this.state.sorting, limit, this.state.offset)
  }

  render() {
    return <div className="catalog">
      <div className="catalog-header">
        <div className="catalog-header-title">
          <h1>Catalog of goods</h1>
        </div>
        <div className="catalog-header-filter">
          <div className="catalog-header-filter-block">
            <span>Sorting by:</span>
            <button className={this.state.sorting === 'id' ? 'active' : ''} onClick={(e)=>this.changeSorting('id')}>ID</button>
            <button className={this.state.sorting === 'price' ? 'active' : ''} onClick={(e)=>this.changeSorting('price')}>Price</button>
          </div>
          <div className="catalog-header-filter-block">
            <span>Limit:</span>
            <button className={this.state.limit === 20 ? 'active' : ''} onClick={(e)=>this.changeLimit(20)}>20</button>
            <button className={this.state.limit === 50 ? 'active' : ''} onClick={(e)=>this.changeLimit(50)}>50</button>
            <button className={this.state.limit === 100 ? 'active' : ''} onClick={(e)=>this.changeLimit(100)}>100</button>
            </div>
        </div>
      </div>
      <div className="catalog-list">
        {Object.values(this.state.goods).map(good=>{
          return <div key={good.id} className="catalog-list-item">
            <div className="catalog-list-item-btns">
              <button onClick={(e)=>this.editGood(e, good.id)} title="Edit"><FA name="pencil-alt"/></button>
              <button onClick={(e)=>this.deleteGood(e, good.id)} title="Delete"><FA name="trash-alt" /></button>
            </div>
            <div className="catalog-list-item-img">
              <img alt={good.title} src={good.img} />
            </div>
            <div className="catalog-list-item-title">{good.title}</div>
            <div className="catalog-list-item-description">{good.description}</div>
            <div className="catalog-list-item-price">Price: <span>{good.price}$</span></div>
          </div>
        })}
      </div>
    </div>
  } 
}
