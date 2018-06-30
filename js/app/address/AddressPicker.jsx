import React from 'react';
import PlacesAutocomplete, { geocodeByAddress } from 'react-places-autocomplete';
import PropTypes from 'prop-types';

const autocompleteOptions = {
  types: ['address'],
  componentRestrictions: {
    country: window.AppData.countryIso || "fr"
  }
}

class AddressPicker extends React.Component {

  constructor(props) {
    super(props);
    this.geohashLib = require('ngeohash');

    let { geohash, address } = this.props;
    // we use `initialAddress` to fill the form with a valid address on blur
    // `address` is used to control the input field
    this.state = {
      initialAddress: address,
      address: address,
      geohash: geohash
    }

    this.insertPreferredResults = this.insertPreferredResults.bind(this);
    this.onAddressSelect = this.onAddressSelect.bind(this);
    this.onAddressChange = this.onAddressChange.bind(this);
    this.onAddressBlur = this.onAddressBlur.bind(this);
    this.onAddressKeyUp = this.onAddressKeyUp.bind(this);
    this.onClear = this.onClear.bind(this);
  }

  onClear () {
    this.setState({address: ''});
  }

  onAddressChange (value) {
    /*
      Controller for the address input text field
     */
    this.setState({address: value});
  }

  onAddressBlur() {
    this.setState({address: this.state.initialAddress})
  }

  onAddressKeyUp(evt) {
    if(evt.key == 'Enter'){
      this.props.onPlaceChange(this.state.geohash, this.state.address);
    }
  }

  onAddressSelect (address, placeId) {
    /*
      Controller for address selection (i.e. click on address in the dropdown)
     */

    geocodeByAddress(address).then(
      (results) => {
        // should always be the case, assert ?
        if (results.length === 1) {
          let place = results[0],
            lat = place.geometry.location.lat(),
            lng = place.geometry.location.lng(),
            geohash = this.geohashLib.encode(lat, lng, 11);
          this.setState({ geohash, address, initialAddress: address });
        }
      }
    );
  }

  shouldComponentUpdate (nextProps, nextState) {

    if (this.state.geohash !== nextState.geohash) { // handle geohash change
      this.props.onPlaceChange(nextState.geohash, nextState.address);
    }

    return true;
  }

  insertPreferredResults ({results}, callback) {
    return callback(this.props.preferredResults.concat(results))
  }

  render () {
    let { preferredResults } = this.props

    // inputProps.onBlur = this.onBlur
    // inputProps.onKeyUp = this.onAddressKeyUp
    // inputProps.placeholder = this.placeholder

    return (
      <div className="autocomplete-wrapper">
        <PlacesAutocomplete
          value={this.state.address}
          onChange={this.onAddressChange}
          onSelect={this.onAddressSelect}
          searchOptions={autocompleteOptions}
        >
          {({ getInputProps, suggestions, getSuggestionItemProps, loading }) => (
            <div className="form-group input-location-wrapper">
              <input
                {...getInputProps({
                  placeholder: 'Type an address…',
                  className: 'form-control input-location',
                })}
              />
              <div style={{ zIndex: 1, backgroundColor: '#fff' }}>
                {suggestions.map(suggestion => {
                  const className = suggestion.active
                    ? 'location-result location-result--active'
                    : 'location-result';
                  return (
                    <div {...getSuggestionItemProps(suggestion, { className })}>
                      <span>{suggestion.description}</span>
                    </div>
                  );
                })}
              </div>
            </div>
          )}
        </PlacesAutocomplete>
        { this.state.address && <i className="fa fa-times-circle autocomplete-clear" onClick={this.onClear}></i> }
      </div>
    );
  }
}

AddressPicker.propTypes = {
  preferredResults:  PropTypes.arrayOf(
    PropTypes.shape({
      suggestion: PropTypes.string.isRequired,
      preferred: PropTypes.bool.isRequired,
  })).isRequired,
  address: PropTypes.string,
  geohash: PropTypes.string.isRequired,
  onPlaceChange: PropTypes.func.isRequired,
}

export default AddressPicker
