import React, {Component} from "react";
import PropTypes from "prop-types";
import Office from "./Office";
import Seat from "./Seat";
import {DndProvider} from "react-dnd";
import {HTML5Backend} from "react-dnd-html5-backend";
import {getSeats, updateSeatCoords} from "../api/seats_api";

export default class OfficeMapApp extends Component {
	constructor(props) {
		super(props);

		this.officeId = props.officeId;

		this.state = {
			chairs: [],
		}

		this.handleDropChair = this.handleDropChair.bind(this);
	}

	componentDidMount() {
		getSeats(this.officeId)
			.then((data) => {
				console.log(data);
				this.setState({
					chairs: data
				});
			});
	}

	handleDropChair(id, coords) {
		this.setState(prevState => {
			const updatedChairs = prevState.chairs.map(chair => {
				if (chair.id === id) {
					return {
						...chair,
						coordX: coords.x,
						coordY: coords.y
					};
				}
				return chair;
			});

			return { chairs: updatedChairs };
		});

		updateSeatCoords(id, coords.x, coords.y)
			.then((data) => {
			console.log("Seat coordinates updated");
			});
	}

	render()    {
		return (
			<DndProvider backend={HTML5Backend}>
				<Office
					onDropChair={this.handleDropChair}
				>
					{this.state.chairs.map(chair => (
						<Seat key={chair.id} id={chair.id} left={chair.coordX} top={chair.coordY} />
					))}
				</Office>
			</DndProvider>
		);
	};
}