import React, { useState, useEffect } from "react";
import PropTypes from "prop-types";
import Office from "./Office";
import { Seat } from "./Seat";
import { DndProvider } from "react-dnd";
import { HTML5Backend } from "react-dnd-html5-backend";
import { getCurrentAssignments, getSeats, updateSeatCoords } from "../api/api";
import PopupWarning from "../Warnings/PopupWarning";

const OfficeMapApp = ({ officeId, officeName }) => {
	const [chairs, setChairs] = useState([]);
	const [showPopup, setShowPopup] = useState(false);
	const [popupMessage, setPopupMessage] = useState("");

	useEffect(() => {
		const fetchSeatsAndAssignments = async () => {
			try {
				const seatData = await getSeats(officeId);
				const chairPromises = seatData.map(async chair => {
					const currentAssignments = await getCurrentAssignments(chair.id);
					return { ...chair, currentAssignments };
				});

				const chairsWithAssignments = await Promise.all(chairPromises);
				setChairs(chairsWithAssignments);
			} catch (error) {
				console.error("Error fetching data", error);
			}
		};

		fetchSeatsAndAssignments();
	}, [officeId]);

	const addNewChair = (newChair) => {
		setChairs([...chairs, newChair]);
	}

	const handleDropChair = async (id, coords) => {
		try {
			const response = await updateSeatCoords(id, coords.x, coords.y);

			if (response.redirected) {
				setShowPopup(true);
				setPopupMessage("Seat coordinates were not updated. Access denied. You need to have admin rights.");
				return;
			}

			const updatedChairs = chairs.map(chair => chair.id === id ? { ...chair, coordX: coords.x, coordY: coords.y } : chair);
			setChairs(updatedChairs);
		} catch (error) {
			console.error("Error updating coordinates", error);
		}
	};

	const showPopupMessage = (message) => {
		setShowPopup(true);
		setPopupMessage(message);
	};

	const closePopup = () => {
		setShowPopup(false);
		setPopupMessage("");
	};

	return (
		<DndProvider backend={HTML5Backend}>
			<Office
				onDropChair={handleDropChair}
				officeId={officeId}
				officeName={officeName}
				showPopupMessage={showPopupMessage} // Passing down the function
				chairs={chairs}
				addNewChair={addNewChair}
			>
				{/*{chairs.map(chair => (*/}
				{/*	<Seat key={chair.id} id={chair.id} left={chair.coordX} top={chair.coordY} currentAssignments={chair.currentAssignments} />*/}
				{/*))}*/}
			</Office>
			{showPopup && <PopupWarning message={popupMessage} onClose={closePopup} />}
		</DndProvider>
	);
};

OfficeMapApp.propTypes = {
	officeId: PropTypes.string.isRequired,
	officeName: PropTypes.string.isRequired,
};

export default OfficeMapApp;
