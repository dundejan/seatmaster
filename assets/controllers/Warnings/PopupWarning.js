import React from 'react';

const PopupWarning = ({ message, onClose }) => {
    const popupStyle = {
        position: 'fixed',
        top: '0',
        left: '0',
        width: '100%',
        height: '100%',
        background: 'rgba(0, 0, 0, 0.5)',
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        // Set a higher z-index to bring the popup to the foreground
        zIndex: '1000',
    };

    const contentStyle = {
        background: 'white',
        padding: '20px',
        borderRadius: '5px',
        textAlign: 'center',
        boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)',
    };

    const buttonStyle = {
        background: 'red',
        color: 'white',
        border: 'none',
        padding: '10px 20px',
        borderRadius: '5px',
        cursor: 'pointer',
        marginTop: '10px',
    };

    return (
        <div style={popupStyle}>
            <div style={contentStyle}>
                <span>{message}</span>
                <button style={buttonStyle} onClick={onClose}>Close</button>
            </div>
        </div>
    );
};

export default PopupWarning;