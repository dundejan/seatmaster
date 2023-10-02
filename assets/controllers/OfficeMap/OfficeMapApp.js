import React, {Component} from "react";
import PropTypes from "prop-types";
import Office from "./Office";
import Seat from "./Seat";
import {DndProvider} from "react-dnd";
import {HTML5Backend} from "react-dnd-html5-backend";
import {getSeats, updateSeatCoords} from "../api/seats_api";

function hasCurrentAssignment(seat) {
	const now = new Date();

	return seat.assignments.find(assignment => {
		const fromDate = new Date(assignment.fromDate);
		const toDate = new Date(assignment.toDate);

		return fromDate.getTime() <= now.getTime() && now.getTime() <= toDate.getTime();
	});
}

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

		console.log(coords.x, coords.y);

		updateSeatCoords(id, coords.x, coords.y)
			.then((data) => {
			console.log("Seat coordinates updated");
			});
	}

	render() {
		return (
			<DndProvider backend={HTML5Backend}>
				<Office
					onDropChair={this.handleDropChair}
					size="400px"
					width="1000px"
					officeId={this.officeId}
				>
					{this.state.chairs.map(chair => (
						<Seat key={chair.id} id={chair.id} left={chair.coordX} top={chair.coordY} occupied={hasCurrentAssignment(chair)} />
					))}
				</Office>
			</DndProvider>
		);
	}
}

OfficeMapApp.propTypes = {
	officeId: PropTypes.string.isRequired,
};