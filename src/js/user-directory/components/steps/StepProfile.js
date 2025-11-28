import React from 'react';
import { __ } from '@wordpress/i18n';

const StepProfile = ({ formData, setFormData, config }) => {
    // Free version only allows layout-2
    const freeLayout = 'layout-2';

    const handleChange = (e) => {
        const { name, value } = e.target;
        
        // In free version, only allow layout-2
        if (name === 'profile_layout' && value !== freeLayout) {
            return; // Ignore clicks on Pro layouts
        }
        
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    // Check both wpuf_ud_free (Free version) and wpuf_ud (Pro version) for layout options
    const layoutOptions = (typeof wpuf_ud_free !== 'undefined' && wpuf_ud_free.profile_layouts) 
        ? wpuf_ud_free.profile_layouts 
        : (typeof wpuf_ud !== 'undefined' && wpuf_ud.profile_layouts ? wpuf_ud.profile_layouts : {});

    // Check if a layout is Pro-only
    const isProLayout = (layout) => layout !== freeLayout;

    return (
        <div className="wpuf-w-full wpuf-bg-white wpuf-max-w-3xl wpuf-mx-auto">
            <h2 className="wpuf-text-center"
                style={{
                    
                    fontWeight: 400,
                    fontSize: '20px',
                    lineHeight: '36px',
                    letterSpacing: '0%',
                    textAlign: 'center',
                    color: '#000000'
                }}
            >
                {__('Profile Layout', 'wp-user-frontend')}
            </h2>
            <p className="wpuf-text-center wpuf-mt-2"
                style={{
                    
                    fontWeight: 400,
                    fontSize: '14px',
                    lineHeight: '20px',
                    letterSpacing: '0%',
                    textAlign: 'center',
                    color: '#64748B'
                }}
            >{__('Decide how individual user profiles will appear. Select a layout that best organizes profile information', 'wp-user-frontend')}</p>
            <div className="wpuf-grid wpuf-grid-cols-3 wpuf-gap-2 wpuf-mt-12">
                {Object.entries(layoutOptions).map(([value, option]) => {
                    const isPro = isProLayout(value);
                    const isSelected = formData.profile_layout === value;
                    
                    return (
                        <label
                            key={value}
                            className={`wpuf-relative wpuf-flex wpuf-flex-col wpuf-items-center wpuf-transition-all wpuf-duration-200 wpuf-group wpuf-overflow-hidden ${
                                isPro ? 'wpuf-cursor-not-allowed' : 'wpuf-cursor-pointer'
                            }`}
                            style={{
                                width: '255px',
                                height: '182px',
                                borderRadius: '10px',
                                borderWidth: isPro ? '2px' : '1px',
                                borderStyle: isPro ? 'dashed' : 'solid',
                                borderColor: isSelected ? '#059669' : '#E5E7EB',
                                backgroundColor: '#FFFFFF',
                                opacity: isPro ? 0.6 : 1,
                                transition: 'all 0.2s ease-in-out'
                            }}
                            onMouseEnter={(e) => { if (isPro) e.currentTarget.style.borderColor = '#3B82F6'; }}
                            onMouseLeave={(e) => { if (isPro) e.currentTarget.style.borderColor = '#E5E7EB'; }}
                        >
                            <input
                                type="radio"
                                name="profile_layout"
                                value={value}
                                checked={isSelected}
                                onChange={handleChange}
                                className="wpuf-sr-only"
                                disabled={isPro}
                            />
                            <img
                                src={option.image}
                                alt={option.name}
                                style={{ 
                                    maxWidth: '100%',
                                    maxHeight: '100%',
                                    padding: '10px',
                                    imageRendering: 'crisp-edges', 
                                    WebkitFontSmoothing: 'antialiased' 
                                }}
                            />
                            
                            {/* Pro Badge for locked layouts */}
                            {isPro && (
                                <div className="wpuf-absolute wpuf-inset-0 wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-bg-white/80" style={{ borderRadius: '10px' }}>
                                    <img 
                                        src={(window.wpuf_ud_free?.asset_url || window.wpuf_ud?.asset_url || '') + '/images/pro-badge.svg'} 
                                        alt="Pro" 
                                        className="wpuf-mb-2"
                                        style={{ width: '39px', height: '22px' }}
                                    />
                                    <span className="wpuf-text-xs wpuf-text-gray-600 wpuf-text-center wpuf-px-2">
                                        {option.name}
                                    </span>
                                </div>
                            )}
                            
                            {/* Hover overlay with layout name - only for free layout */}
                            {!isPro && (
                                <div 
                                    className="wpuf-absolute wpuf-inset-0 wpuf-flex wpuf-items-center wpuf-justify-center wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-transition-opacity wpuf-duration-300"
                                    style={{
                                        backgroundColor: 'rgba(236, 253, 245, 0.95)',
                                        borderRadius: '10px'
                                    }}
                                >
                                    <span 
                                        style={{
                                            fontSize: '16px',
                                            fontWeight: 600,
                                            color: '#059669'
                                        }}
                                    >{option.name}</span>
                                </div>
                            )}
                            
                            {isSelected && !isPro && (
                                <span 
                                    className="wpuf-absolute wpuf-flex wpuf-items-center wpuf-justify-center wpuf-z-10"
                                    style={{
                                        width: '20px',
                                        height: '20px',
                                        borderRadius: '23px',
                                        backgroundColor: '#059669',
                                        top: '12px',
                                        right: '12px',
                                        opacity: 1
                                    }}
                                >
                                    <svg width="8" height="6" viewBox="0 0 8 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1.49927 2.5L3.49927 4.5L6.99927 1" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                    </svg>
                                </span>
                            )}
                        </label>
                    );
                })}
            </div>
        </div>
    );
};

export default StepProfile;
