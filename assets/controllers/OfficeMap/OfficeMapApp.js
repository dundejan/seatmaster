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

function hasCurrentRepeatedAssignment(seat) {
	const now = new Date();

	// Retrieve day of the week (1 for Monday, 2 for Tuesday, etc.)
	const currentDayOfWeek = now.getUTCDay() === 0 ? 7 : now.getUTCDay();

	return seat.repeatedAssignments.find(repeatedAssignment => {
		const { dayOfWeek, fromTime, toTime } = repeatedAssignment;

		const fromTimeDate = new Date(fromTime);
		const toTimeDate = new Date(toTime);

		const from = fromTimeDate.getUTCHours() * 60 + fromTimeDate.getUTCMinutes();
		const to = toTimeDate.getUTCHours() * 60 + toTimeDate.getUTCMinutes();
		const nowTime = now.getHours() * 60 + now.getMinutes();

		// Check if today is the right day of the week and current time is within fromTime and toTime
		return currentDayOfWeek === dayOfWeek &&
			from <= nowTime &&
			nowTime <= to;
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
						<Seat key={chair.id} id={chair.id} left={chair.coordX} top={chair.coordY} occupied={hasCurrentAssignment(chair) || hasCurrentRepeatedAssignment(chair)} />
					))}
				</Office>
			</DndProvider>
		);
	}
}

OfficeMapApp.propTypes = {
	officeId: PropTypes.string.isRequired,
};